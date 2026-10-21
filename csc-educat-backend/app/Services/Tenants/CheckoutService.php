<?php

namespace App\Services\Tenants;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Helpers\StandaloneConfig;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\Enrollment;
use App\Models\PrimeResolution;
use App\Models\EndorsementLetter;
use App\Models\NominationForm;
use Auth;
use Storage;

class CheckoutService
{
    public static function auth()
    {
        return Auth::guard('api')->user();
    }

    public static function refId()
    {
        $format = 'ABCDEF';
        $randStr = str_replace(
            ['A', 'B', 'C', 'D', 'E', 'F'],
            [
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
                Str::upper(Str::random(1)),
            ],
            $format
        );

        return date('ymhis') . $randStr;
    }

    /**
     * Rollback uploaded files and delete the invoice.
     */
    public static function rollbackFiles($invoice, $files)
    {
        foreach ($files as $fileKey => $model) {
            $model::where('invoice_id', $invoice->id)->delete();
        }
        $invoice->delete();
    }

    public static function uploadRequirement($path, $file, $type)
    {
        try {
            if (config('app.env') === 'prod') {
                Log::info("Uploading $type to S3...");
                $fullPath = Storage::disk('s3')->put($path, $file);
                return Storage::disk('s3')->url($fullPath);
            } else {
                Log::info("Uploading $type to local storage...");
                $fullPath = Storage::disk('public')->put($path, $file);
                return asset($fullPath);
            }
        } catch (\Exception $e) {
            Log::error("Error uploading $type: " . $e->getMessage());
            throw $e;
        }
    }

    public static function gatewayCheckout($request)
    {

        $course = Course::where('id', $request['course_id'])
            ->first(['id', 'title', 'price', 'is_free'])
            ->makeHidden([
                'course_card',
                'total_done_assessments',
                'total_done_topics',
                'total_done_sub_topics',
                'total_done_course_evaluations',
                'total_done_module_evaluations',
                'total_attended_zoom_meetings',
                'total_done_group_activities',
                'total_time_spent',
                'course_card',
                'image'
            ]);
        
            Log::info('gatewayCheckout');

        if ($course->price > 0) {
            // Online payment gateway is disabled in this deployment.
            return response()->json('Online payment is not available. Please choose another type of payment.', 422);

            $user = User::where('id', self::auth()->id)->first();

            $reference_id = self::refId();

            $invoice = Invoice::create([
                'reference_id' => $reference_id,
                'user_id' => self::auth()->id,
                'course_id' => $course->id,
                'mode_of_payment' => Invoice::ONLINE,
                'type_of_payment' => $request['type_of_payment'],
                'type_of_participant' => $request['type_of_participant'],
                'amount' => $course->price,
                'is_free' => $course->is_free
            ]);

            $files = [
                'endorsement_letter' => EndorsementLetter::class,
                'prime_resolution' => PrimeResolution::class,
            ];
    
            foreach ($files as $fileKey => $model) {
                if ($request[$fileKey]) {
                    try {
                        $fileRecord = $model::create(['invoice_id' => $invoice->id, 'url' => '']);
                        $path = "{$fileKey}/{$fileRecord->id}";
                        $url = CheckoutService::uploadRequirement($path, $request[$fileKey], str_replace('_', ' ', $fileKey));
                        $fileRecord->update(['url' => $url]);

                    } catch (\Exception $e) {
                        CheckoutService::rollbackFiles($invoice, $files);
                        Log::error("Upload requirement error: " . $e->getMessage());
                        return response()->json('An error occurred while uploading a requirement.', 500);
                    }
                }
            }

            $notify_url = StandaloneConfig::apiEndpoint() . '/v1/tenants/checkout/callback';
            $notify_url = 'https://'.StandaloneConfig::apiEndpoint() . '/v1/tenants/checkout/callback';
            $hash = md5(self::auth()->email . $course->price . config('services.payment_gateway.secret'));
            $data = [
                'ref_id' => $reference_id,
                'amount' => $course->price,
                'email' => self::auth()->email,
                'form_id' => 175,
                'loc_id' => 5,
                'first_name' => self::auth()->first_name,
                'middle_name' => self::auth()->middle_name,
                'last_name' => self::auth()->last_name,
                'notify_url' => $notify_url,
                'hash' => $hash,
                'service_name_spicify' => $course->title,
                'service_other_details' => ''
            ];

            $response = Http::asForm()->post(config('services.payment_gateway.url') . '/forwarder', $data);

            Log::info('data: '.json_encode($data));
            Log::info('checkout: '.$response);
            Log::info($response->body());
            if (isset($response['result'])) {
                if ($response['result'] == 'error') {
                    foreach ($files as $fileKey => $model) {
                        CheckoutService::rollbackFiles($invoice, $files);
                    }

                    activity('course_checkout')
                        ->causedBy($user)
                        ->withProperties([
                            'user_id' => $invoice->user_id,
                            'email' => $user->email,
                            'course_id' => $invoice->course_id,
                            'invoice_data' => json_encode($invoice),
                        ])
                        ->event('created')
                        ->performedOn($user)
                        ->log('User Checkout');
                        
                    return response()->json($response['result_message'], 422);
                }
            }

        } else {
            
            $ref_id = self::refId();
            $invoice = Invoice::create([
                'reference_id' => $ref_id,
                'user_id' => self::auth()->id,
                'course_id' => $course->id,
                'mode_of_payment' => Invoice::OFFLINE,
                'status' => Invoice::PAYMENT_FREE,
                'amount' => $course->price,
                'is_free' => $course->is_free,
                'type_of_payment' => $request['type_of_payment'] ?? '',
                'type_of_participant' => $request['type_of_participant'],
            ]);

            $files = [
                'endorsement_letter' => EndorsementLetter::class,
                'prime_resolution' => PrimeResolution::class,
            ];
    
            foreach ($files as $fileKey => $model) {
                if ($request[$fileKey]) {
                    try {
                        $fileRecord = $model::create(['invoice_id' => $invoice->id, 'url' => '']);
                        $path = "{$fileKey}/{$fileRecord->id}";
                        $url = CheckoutService::uploadRequirement($path, $request[$fileKey], str_replace('_', ' ', $fileKey));
                        $fileRecord->update(['url' => $url]);
                    } catch (\Exception $e) {
                        CheckoutService::rollbackFiles($invoice, $files);
                        Log::error("Upload requirement error: " . $e->getMessage());
                        return response()->json('An error occurred while uploading a requirement.', 500);
                    }
                }
            }
            
            $enrollment = Enrollment::where('user_id', self::auth()->id)->where('course_id', $course->id)->exists();

            Enrollment::firstOrCreate([
                'user_id' => self::auth()->id,
                'course_id' => $course->id
            ]);
    
            $user = User::where('id', self::auth()->id)->first();

            if(!$enrollment) {
                activity('user_enrollment')
                    ->causedBy($user)
                    ->withProperties([
                        'user_id' => self::auth()->id,
                        'email' => $user->email,
                        'course_id' => $course->id,
                    ])
                    ->event('created')
                    ->performedOn($user)
                    ->log('User Enrolled');
            }

            activity('course_checkout')
                ->causedBy($user)
                ->withProperties([
                    'user_id' => $invoice->user_id,
                    'email' => $user->email,
                    'course_id' => $invoice->course_id,
                    'invoice_data' => json_encode($invoice),
                ])
                ->event('created')
                ->performedOn($user)
                ->log('User Checkout');

            // if (empty($request['nomination_form'])) {
            //     $enrollment = Enrollment::where('user_id', self::auth()->id)->where('course_id', $course->id)->exists();

            //     Enrollment::firstOrCreate([
            //         'user_id' => self::auth()->id,
            //         'course_id' => $course->id
            //     ]);
                
            //     if(!$enrollment) {
            //         $user = User::where('id', self::auth()->id)->first();

            //         activity('user_enrollment')
            //             ->causedBy($user)
            //             ->withProperties([
            //                 'user_id' => self::auth()->id,
            //                 'course_id' => $course->id,
            //             ])
            //             ->event('created')
            //             ->performedOn($user)
            //             ->log('User Enrolled');
            //     }
                
            // }
            $response = response()->json([
                'mode_of_payment' => 'offline',
                'msg' => 'automatic enroll.'
            ]);
        }

        self::auth()->cart_item_courses()->detach($request['course_id']);

        return $response;
    }
}
