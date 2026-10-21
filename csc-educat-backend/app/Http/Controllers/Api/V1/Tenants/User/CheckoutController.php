<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\CommitmentLetter;
use Illuminate\Http\Request;
use App\Services\Tenants\CheckoutService;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Course;
use App\Models\EndorsementLetter;
use App\Models\NominationForm;
use App\Models\Enrollment;
use App\Models\PrimeResolution;
use App\Models\ProofOfPayment;
use Storage;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
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

    public function checkout(Request $request)
    {
        //if (tenancy()->tenant->id == 'default') {
            if($request->nomination_form) {
                $request->validate([
                    'course_id' => 'required|exists:courses,id',
                ]
                );
            } 
            
            $request->validate([
                'type_of_participant' => 'required|in:internal_employee,regular_payee,regular_payee_hrm',
                'prime_resolution' => 'required_if:type_of_participant,regular_payee_hrm|mimes:pdf,doc,docx',
                'endorsement_letter' => 'required_if:type_of_participant,regular_payee_hrm|mimes:pdf,doc,docx',
            ], [
                'prime_resolution.required_if' => 'PRIME Resolution is required.',
                'endorsement_letter.required_if' => 'Endorsement Letter is required.',
            ]);


            $invoice = Invoice::pendingOrCommitted($request->course_id, $this->authUser()->id)->first();

            if($invoice) {
                return $this->sendResponse([], 'You have a pending transaction with this course, check your transaction page', 409);
            }

            $enrollment = Enrollment::where('user_id', $this->authUser()->id)->where('course_id', $request->course_id)->exists();

            if($enrollment) {
                return $this->sendResponse([], 'You are currently enrolled in this course', 409);
            }

            $checkout = CheckoutService::gatewayCheckout([
                'course_id' => $request->course_id,
                'prime_resolution' => $request->hasFile('prime_resolution') ? $request->file('prime_resolution') : null,
                'endorsement_letter' => $request->hasFile('endorsement_letter') ? $request->file('endorsement_letter') : null,
                'type_of_participant' => $request->type_of_participant,
                'type_of_payment' => $request->type_of_payment,
            ]);

            return $checkout;
        // } else {
        //     return response()->json('No checkout functions found.', 404);
        //}
    }

    public function checkoutOnlinePortal(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'type_of_payment' => 'required|string',
            'type_of_participant' => 'required|in:internal_employee,regular_payee,regular_payee_hrm',
            'prime_resolution' => 'required_if:type_of_participant,regular_payee_hrm|mimes:pdf,doc,docx|max:10240',
            'endorsement_letter' => 'required_if:type_of_participant,regular_payee_hrm|mimes:pdf,doc,docx|max:10240',
        ], [
            'prime_resolution.required_if' => 'PRIME Resolution is required.',
            'endorsement_letter.required_if' => 'Endorsement Letter is required.',
            'prime_resolution.max' => 'The file must not be larger than 10MB.',
            'endorsement_letter.max' => 'The file must not be larger than 10MB.',
        ]);

        $invoice = Invoice::pendingOrCommitted($request->course_id, $this->authUser()->id)->first();

        if($invoice) {
            return $this->sendResponse([], 'You have a pending transaction with this course, check your transaction page', 409);
        }

        $enrollment = Enrollment::where('user_id', $this->authUser()->id)->where('course_id', $request->course_id)->exists();

        //$invoice = Invoice::where('course_id', $request->course_id)->where('user_id', $this->authUser()->id)->where('status', 1)->first();

        if($enrollment) {
            return $this->sendResponse([], 'You are currently enrolled in this course', 409);
        }

        $checkout = CheckoutService::gatewayCheckout([
            'course_id' => $request->course_id,
            'type_of_payment' => $request->type_of_payment,
            'type_of_participant' => $request->type_of_participant,
            'prime_resolution' => $request->hasFile('prime_resolution') ? $request->file('prime_resolution') : null,
            'endorsement_letter' => $request->hasFile('endorsement_letter') ? $request->file('endorsement_letter') : null,
        ]);

        return $checkout;
    }

    
    public function checkoutNomination(Request $request)
    {
        Log::info('checkout nomination start');
        
        /* try { */
            Log::info('checkout nomination start validate');
            
            $request->validate([
                    'course_id' => 'required|exists:courses,id',
                    'type_of_payment' => 'required|string',
                    'type_of_participant' => 'required|in:internal_employee,regular_payee,regular_payee_hrm',
                    'proof_of_payment' => 'required_unless:type_of_payment,commitment_letter|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                    'prime_resolution' => 'required_if:type_of_participant,regular_payee_hrm|mimes:pdf,doc,docx|max:10240',
                    'endorsement_letter' => 'required_if:type_of_participant,regular_payee_hrm|mimes:pdf,doc,docx|max:10240',
                    'commitment_letter' => 'required_if:type_of_payment,commitment_letter|mimes:pdf,doc,docx|max:10240',
                    'reference_number' => 'required_with:proof_of_payment|string',
                ], [
                    'reference_number.required_with' => 'Payment Reference Number is required',
                    'proof_of_payment.required_unless' => 'Proof of Payment is required',
                    'prime_resolution.required_if' => 'PRIME Resolution is required.',
                    'endorsement_letter.required_if' => 'Endorsement Letter is required.',
                    'commitment_letter.required_if' => 'Commitment Letter is required.',
                    'proof_of_payment.max' => 'The file must not be larger than 10MB.',
                    'prime_resolution.max' => 'The file must not be larger than 10MB.',
                    'endorsement_letter.max' => 'The file must not be larger than 10MB.',
                    'commitment_letter.max' => 'The file must not be larger than 10MB.',
                ]
            );
            
            Log::info('checkout nomination done validate');

            $invoice = Invoice::pendingOrCommitted($request->course_id, $this->authUser()->id)->first();
            
            Log::info('checkout nomination search invoice');

            if($invoice) {
                return $this->sendResponse([], 'You have a pending transaction with this course, check your transaction page', 409);
            }

            $enrollment = Enrollment::where('user_id', $this->authUser()->id)->where('course_id', $request->course_id)->exists();


            if($enrollment) {
                return $this->sendResponse([], 'You are currently enrolled in this course', 409);
            }

            
            Log::info('checkout nomination search course');

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

            Log::info('checkout nomination generate reference id');

            $ref_id = self::refId();

            $invoice = Invoice::create([
                'reference_id' => $ref_id,
                'user_id' => $this->authUser()->id,
                'course_id' => $course->id,
                'mode_of_payment' => Invoice::OFFLINE,
                'amount' => $course->price,
                'is_free' => $course->is_free,
                'type_of_payment' => $request->type_of_payment,
                'type_of_participant' => $request->type_of_participant,
            ]);

            $files = [
                'endorsement_letter' => EndorsementLetter::class,
                'prime_resolution' => PrimeResolution::class,
                'proof_of_payment' => ProofOfPayment::class,
                'commitment_letter' => CommitmentLetter::class,
            ];
    
            foreach ($files as $fileKey => $model) {
                if ($request->hasFile($fileKey)) {
                    try {
                        if($fileKey == 'proof_of_payment') {
                            $fileRecord = $model::create(['invoice_id' => $invoice->id, 'url' => '', 'reference_number' => $request->reference_number]);
                        } else  {
                            $fileRecord = $model::create(['invoice_id' => $invoice->id, 'url' => '']);
                        }
                        $path = "{$fileKey}/{$fileRecord->id}";
                        $url = $this->uploadRequirement($path, $request->file($fileKey), str_replace('_', ' ', $fileKey));
                        $fileRecord->update(['url' => $url]);

                    } catch (\Exception $e) {
                        $this->rollbackFiles($invoice, $files);
                        Log::error("Upload requirement error: " . $e->getMessage());
                        return $this->sendResponse([], 'An error occurred while uploading a requirement.', 500);
                    }
                }
            }

            $user = User::find($this->authUser()->id);
            
            if($course->price == 0) {

                Enrollment::firstOrCreate([
                    'user_id' => $user->id,
                    'course_id' => $course->id
                ]);
        
                activity('user_enrollment')
                    ->causedBy($user)
                    ->withProperties([
                        'user_id' => $user->id,
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

            return $this->sendResponse([
                'mode_of_payment' => 'offline',
                'msg' => 'automatic enroll.',
                'invoice' => $invoice
            ], 'Successfully sent nomination and proof of payment', 200);
            
        /* } catch(\Exception $e) {

            Log::error('Checkout Error' . $e);
            return $this->sendResponse([], 'Error on checkout', 500);
        } */
    } 


    public function uploadRequirement($path, $file, $type)
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
    
    public function checkoutOwnAccount(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'type_of_payment' => 'required|string',
            'type_of_participant' => 'required|in:internal_employee,regular_payee,regular_payee_hrm',
            'commitment_letter' => 'required_if:type_of_payment,commitment_letter|mimes:pdf,doc,docx|max:10240',
            'prime_resolution' => 'required_if:type_of_participant,regular_payee_hrm|mimes:pdf,doc,docx|max:10240',
            'endorsement_letter' => 'required_if:type_of_participant,regular_payee_hrm|mimes:pdf,doc,docx|max:10240',
            'proof_of_payment' => 'sometimes|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'reference_number' => 'required_with:proof_of_payment|string',
        ], [
            'reference_number.required_with' => 'Payment Reference Number is required',
            'proof_of_payment.required_unless' => 'Proof of Payment is required',
            'commitment_letter.required_if' => 'Commitment Letter is required.',
            'prime_resolution.required_if' => 'PRIME Resolution is required.',
            'endorsement_letter.required_if' => 'Endorsement Letter is required.',
            'commitment_letter.max' => 'The file must not be larger than 10MB.',
            'prime_resolution.max' => 'The file must not be larger than 10MB.',
            'endorsement_letter.max' => 'The file must not be larger than 10MB.',
            'proof_of_payment.max' => 'The file must not be larger than 10MB.',
        ]);

        if (
            $request->type_of_participant != Invoice::PARTICIPANT_PRIME &&
            !$request->hasFile('commitment_letter') &&
            !$request->hasFile('endorsement_letter') &&
            !$request->hasFile('prime_resolution') &&
            !$request->hasFile('proof_of_payment')
        ) {
            return $this->sendResponse([], 'Proof of payment is required', 422);
        }

        $invoice = Invoice::pendingOrCommitted($request->course_id, $this->authUser()->id)->first();

        if ($invoice) {
            return $this->sendResponse([], 'You have a pending transaction with this course, check your transaction page', 409);
        }

        $enrollment = Enrollment::where('user_id', $this->authUser()->id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($enrollment) {
            return $this->sendResponse([], 'You are currently enrolled in this course', 409);
        }

        $course = Course::where('id', $request->course_id)
            ->first(['id', 'title', 'price', 'is_free'])
            ->makeHidden([
                'course_card', 'total_done_assessments', 'total_done_topics',
                'total_done_sub_topics', 'total_done_course_evaluations',
                'total_done_module_evaluations', 'total_attended_zoom_meetings', 'total_time_spent',
                'total_done_group_activities',
                'course_card', 'image',
            ]);

        $ref_id = self::refId();

        $invoice = Invoice::create([
            'reference_id' => $ref_id,
            'user_id' => $this->authUser()->id,
            'course_id' => $course->id,
            'mode_of_payment' => Invoice::OFFLINE,
            'amount' => $course->price,
            'is_free' => $course->is_free,
            'type_of_payment' => $request->type_of_payment,
            'type_of_participant' => $request->type_of_participant,
        ]);
        
        // Process uploads
        $files = [
            'commitment_letter' => CommitmentLetter::class,
            'endorsement_letter' => EndorsementLetter::class,
            'prime_resolution' => PrimeResolution::class,
            'proof_of_payment' => ProofOfPayment::class,
        ];

        foreach ($files as $fileKey => $model) {
            if ($request->hasFile($fileKey)) {
                try {
                    if($fileKey == 'proof_of_payment') {
                        $fileRecord = $model::create(['invoice_id' => $invoice->id, 'url' => '', 'reference_number' => $request->reference_number]);
                    } else  {
                        $fileRecord = $model::create(['invoice_id' => $invoice->id, 'url' => '']);
                    }
                    $path = "{$fileKey}/{$fileRecord->id}";
                    $url = $this->uploadRequirement($path, $request->file($fileKey), str_replace('_', ' ', $fileKey));
                    $fileRecord->update(['url' => $url]);

                } catch (\Exception $e) {
                    $this->rollbackFiles($invoice, $files);
                    Log::error("Upload requirement error: " . $e->getMessage());
                    return $this->sendResponse([], 'An error occurred while uploading a requirement.', 500);
                }
            }
        }

        $user = User::find($this->authUser()->id);

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


        return $this->sendResponse(['invoice' => $invoice], 'Requirement uploaded successfully.', 200);
    }

    /**
     * Rollback uploaded files and delete the invoice.
     */
    private function rollbackFiles($invoice, $files)
    {
        foreach ($files as $fileKey => $model) {
            $model::where('invoice_id', $invoice->id)->delete();
        }
        $invoice->delete();
    }

    public function checkoutOffline(Request $request)
    {
        Log::info($request->all());

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'type_of_payment' => 'required',
            'proof_of_payment' => 'required|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'reference_number' => 'required_with:proof_of_payment|string',
        ], [
            'proof_of_payment.max' => 'The file must not be larger than 10MB.',
        ]);

        $invoice = Invoice::pendingOrCommitted($request->course_id, $this->authUser()->id)->first();

        if($invoice) {
            return $this->sendResponse([], 'You have a pending transaction with this course, check your transaction page', 409);
        }

        $enrollment = Enrollment::where('user_id', $this->authUser()->id)->where('course_id', $request->course_id)->exists();

        if($enrollment) {
            return $this->sendResponse([], 'You are currently enrolled in this course', 409);
        }

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

        $ref_id = self::refId();

        $invoice = Invoice::create([
            'reference_id' => $ref_id,
            'user_id' => $this->authUser()->id,
            'course_id' => $course->id,
            'mode_of_payment' => Invoice::OFFLINE,
            'type_of_payment' => $request->type_of_payment,
            'amount' => $course->price,
            'is_free' => $course->is_free
        ]);

        $proof_of_payment = ProofOfPayment::create([
            'invoice_id' => $invoice->id,
            'reference_number' => $request->reference_number
        ]);

        $path_proof_of_payment = "proof_of_payment/{$proof_of_payment->id}";

        if (config('app.env') == 'prod') {
            try {
                // store proof_of_payment
                $proof_of_payment_path = Storage::disk('s3')->put($path_proof_of_payment, $request->proof_of_payment);
                $proof_of_payment_url = Storage::disk('s3')->url($proof_of_payment_path);

            } catch(\Exception $e) {
                
                Log::error('Proof of payment uploading error' . $e);

                $proof_of_payment->delete();
                $invoice->delete();
                return $this->sendResponse([], 'Error in uploading proof of payment', 502);
            }

        } else {
            $proof_of_payment_path = Storage::disk('public')->put($path_proof_of_payment, $request->proof_of_payment);
            $proof_of_payment_url = asset($proof_of_payment_path);
        }
        
        $proof_of_payment->update([
            'url' => $proof_of_payment_url
        ]);

        $user = User::find($this->authUser()->id);

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


        return $this->sendResponse([
            'mode_of_payment' => 'offline',
            'msg' => 'automatic enroll.'
        ], 'Successfully sent proof of payment', 200);
    }
}
