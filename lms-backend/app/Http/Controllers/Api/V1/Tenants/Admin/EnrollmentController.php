<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Services\Tenants\ScormCloudService;

use App\Imports\Tenants\EnrolleesImport;
use App\Exports\Tenants\Admin\InvoiceExport;
use App\Exports\Tenants\Admin\CourseInvoicesExport;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Helpers\StandaloneConfig;

use App\Http\Controllers\Controller;
use App\Jobs\Tenants\ProcessAdminInvoicesListExport;
use App\Jobs\Tenants\ProcessAdminCourseInvoicesExport;
use App\Jobs\Tenants\SendManualCourseEnrollmentApproveEmail;
use App\Jobs\Tenants\SendManualCourseEnrollmentDeclineEmail;
use App\Jobs\Tenants\SendManualCourseEnrollmentDisapproveEmail;
use App\Jobs\Tenants\SendSuccessfulEnrollmentEmail;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Mail\Tenants\Course\CourseEnrollment;
use App\Mail\Tenants\Course\ManualCourseEnrollmentApprove;
use App\Mail\Tenants\Course\ManualCourseEnrollmentDecline;
use App\Mail\Tenants\Course\ManualCourseEnrollmentDisapprove;
use App\Models\CommitmentLetter;
use App\Services\Tenants\CheckoutService;

use App\Models\ProofOfPayment;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\ImportLog;
use App\Models\EndorsementLetter;
use App\Models\Exports;
use App\Models\NominationForm;
use App\Models\PrimeResolution;
use Illuminate\Support\Str;

use Excel;
use Exception;
use Storage;


class EnrollmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
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

    public function showCourses(Request $request)
    {
        $courses = Course::select(['title', 'uploader_id', 'uuid', 'status', 'type_of_learning', 'created_at', 'available_until', 'course_code'])
            ->with([
                'uploader' => fn($uploader) => $uploader->select(['id', 'first_name', 'last_name', 'suffix'])
            ])
            ->when($request->search, function ($query) use ($request) {
                FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
            })
            ->when($request->type_of_learning, function ($query) use ($request) {
                $query->where('type_of_learning', $request->type_of_learning);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status );
            })
            ->when(($request->created_from || $request->created_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->created_from, $request->created_to);
            })
            ->withCount([
                'invoices as pending_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_PENDING);
                },
                'invoices as success_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_SUCCESS);
                },
                'invoices as fail_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_FAILED);
                },
                'invoices as cancel_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_CANCELLED);
                },
                'invoices as commitment_letters' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_COMMITMENT_LETTER);
                },
                'invoices as free_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_FREE);
                },
                'invoices as disapproved_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_DISAPPROVED);
                },
            ])
            ->paginate($request->paginate);

        return $this->sendResponse($courses, 'List of all courses.', 200);
    }

    public function showCourseEnrollees(Request $request, $course_uuid)
    {
        $course = Course::where('uuid', $course_uuid)->first();

        if (!$course) {
            return $this->sendResponse([], 'No course found.', 404);
        }
        $searchParts1 = explode(' ', $request->search);
        $searchParts2 = explode(' ', $request->enrolled_by);

        $invoices = Invoice::whereHas('enrollee')
            ->with([
                'enroll_by', 
                'nomination_form', 
                'image', 
                'proof_of_payment',
                'commitment_letter',
                'prime_resolution',
                'endorsement_letter',
                'enrollee' => function($query) use ($course) {
                    $query->select(['id', 'first_name', 'last_name', 'suffix', 'email', 'uuid', 'data'])
                        ->with(['enrollments' => function($subQuery) use ($course) {
                            $subQuery->where('course_id', $course->id);
                        }]);
                }
            ])
            ->where('course_id', $course->id)
            ->when($request->invoice_status, function ($query) use ($request) {
                $query->where('status', $request->invoice_status);
            })
            ->when($request->mode_of_payment, function ($query) use ($request) {
                $query->where('mode_of_payment', $request->mode_of_payment);
            })
            ->when($request->type_of_payment, function ($query) use ($request) {
                $query->where('type_of_payment', $request->type_of_payment);
            })
            ->when($request->receipt_pick_up_option, function ($query) use ($request) {
                $query->where('receipt_pick_up_option', $request->receipt_pick_up_option);
            })
            ->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
            })
            ->when($request->search, function ($query) use ($request, $searchParts1) {
                $query->whereHas('enrollee', function ($innerQuery) use ($request, $searchParts1) {
                    Helper::queryNameEmailSearch($searchParts1, $innerQuery);
                });
            })
            ->when($request->enrolled_by, function ($query) use ($request, $searchParts2) {
                $query->whereHas('enroll_by', function ($innerQuery) use ($request, $searchParts2) {
                    Helper::queryNameSearch($searchParts2, $innerQuery);
                });
            })
            ->when($request->enrollment_status == 2, function ($query) use ($course) {
                $query->whereHas('enrollee', function ($innerQuery) use ($course) {
                    $innerQuery->whereDoesntHave('enrollments', function ($subQuery) use ($course) {
                        $subQuery->where('course_id', $course->id);
                    });
                });
            })
            ->when($request->enrollment_status == 1, function ($query) use ($course) {
                $query->whereHas('enrollee', function ($innerQuery) use ($course) {
                    $innerQuery->whereHas('enrollments', function ($subQuery) use ($course) {
                        $subQuery->where('course_id', $course->id);
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->paginate);

        return $this->sendResponse([ 'title' => $course->title, 'invoices' => $invoices], 'List of all invoices.', 200);
    }

    public function updateTypeOfPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'type_of_payment' => 'required|string'
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)->first();

        $invoice->update(['type_of_payment' => $request->type_of_payment]);

        return $this->sendResponse([], 'Type of payment updated!', 200);
    }

    public function updateTypeOfParticipant(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'type_of_participant' => 'required|in:internal_employee,regular_payee,regular_payee_hrm',
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)->first();

        $invoice->update(['type_of_participant' => $request->type_of_participant]);

        return $this->sendResponse([], 'Type of participant updated!', 200);
    }

    public function updateInvoiceStatus(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'status' => 'required|in:1,2,3,4,5,6,7'
        ]);

        $invoice = Invoice::with(['enrollee', 'course'])->where('id', $request->invoice_id)->first();

        /* if ($invoice->mode_of_payment == Invoice::ONLINE) {
            return $this->sendResponse([], 'Method is not allowed when mode of payment is ONLINE.', 405);
        }
 */
        $invoice->update(['status' => $request->status, 'for_verification' => false]);

        $date_conducted_str = '';

        if (isset($invoice->course->available_until['start_date'], $invoice->course->available_until['end_date'])) {
            $startDate = strtotime($invoice->course->available_until['start_date']);
            $endDate = strtotime($invoice->course->available_until['end_date']);

            if($invoice->course->type_of_learning == Course::IS_BLENDED) {
                if (date('Y', $startDate) == date('Y', $endDate)) {
                    if ($invoice->course->available_until['start_date'] == $invoice->course->available_until['end_date']) {
                        $date_conducted_str = 'on ' . date('F d, Y', $endDate);
                    } else {
                        $date_conducted_str = 'on ' . date('F d', $startDate) . ' - ' . date('F d, Y', $endDate);
                    }
                } else {
                    $date_conducted_str = 'on ' . date('F d, Y', $startDate) . ' - ' . date('F d, Y', $endDate);
                }
            } else {
                if (date('Y', $startDate) == date('Y', $endDate)) {
                    if ($invoice->course->available_until['start_date'] == $invoice->course->available_until['end_date']) {
                        $date_conducted_str = 'from ' . date('F d, Y', $endDate);
                    } else {
                        $date_conducted_str = 'from ' . date('F d', $startDate) . ' until ' . date('F d, Y', $endDate);
                    }
                } else {
                    $date_conducted_str = 'from ' . date('F d, Y', $startDate) . ' until ' . date('F d, Y', $endDate);
                }
            }
        } else if(isset($invoice->course->available_until['start_date']) && !isset($invoice->course->available_until['end_date'])) {

            $startDate = strtotime($invoice->course->available_until['start_date']);
            $date_conducted_str = 'from ' . date('F d, Y', $startDate);

        } else if(!isset($invoice->course->available_until['start_date']) && isset($invoice->course->available_until['end_date'])) {

            $endDate = strtotime($invoice->course->available_until['end_date']);
            $date_conducted_str = 'until ' . date('F d, Y', $endDate);

        }

        $data = [
            'name' => $invoice->enrollee->full_name,
            'course_uuid' => $invoice->course->uuid,
            'course_title' => $invoice->course->title,
            'type_of_learning' => $invoice->course->type_of_learning,
            'course_description' => $invoice->course->description,
            'course_image' => $invoice->course->image ? $invoice->course->image->url : NULL,
            'url' => StandaloneConfig::domainName(),
            'tenant_id' => StandaloneConfig::tenantId(),
            'email' => $invoice->enrollee->email,
            'date_conducted_str' => $date_conducted_str
        ];

        if ($invoice->status == '1') {
            $enrollment = Enrollment::where('user_id', $invoice->user_id)->where('course_id', $invoice->course_id)->exists();

            $user = User::find($this->authUser()->id);
            $invoice_user = User::find($invoice->user_id);

            Enrollment::firstOrCreate([
                'user_id' => $invoice->user_id,
                'course_id' => $invoice->course_id
            ]);

            if(!$enrollment) {
                activity('user_enrollment')
                    ->causedBy($user)
                    ->withProperties([
                        'user_id' => $invoice->user_id,
                        'email' => $user->email,
                        'course_id' => $invoice->course_id,
                    ])
                    ->event('created')
                    ->performedOn($invoice_user)
                    ->log('User Enrolled');
                
            }

            $apiKey = config("services.sender.key");

            if($apiKey) {
                try {
                    SendManualCourseEnrollmentApproveEmail::dispatch($data);
                } catch (Exception $e) {
                    Log::error($e);
                }
            } else {
                Mail::to($invoice->enrollee->email)->send(new ManualCourseEnrollmentApprove($data));
            }
            
        } else if ($invoice->status == Invoice::PAYMENT_FAILED) {
            $apiKey = config("services.sender.key");

            if($apiKey) {
                try {
                    SendManualCourseEnrollmentDeclineEmail::dispatch($data);
                } catch (Exception $e) {
                    Log::error($e);
                }
            } else {
                Mail::to($invoice->enrollee->email)->send(new ManualCourseEnrollmentDecline($data));
            }
        } else if ($invoice->status == Invoice::PAYMENT_DISAPPROVED) {
            $apiKey = config("services.sender.key");

            if($apiKey) {
                try {
                    SendManualCourseEnrollmentDisapproveEmail::dispatch($data);
                } catch (Exception $e) {
                    Log::error($e);
                }
            } else {
                Mail::to($invoice->enrollee->email)->send(new ManualCourseEnrollmentDisapprove($data));
            }
            
        }

        return $this->sendResponse([], 'Status updated!', 200);
    }

    //functions from V1
    public function showEnrolledCourses($user_id)
    {
        $user = User::with([
            'enrollments' => function ($enrollments) {
                return $enrollments->with([
                    'course' => function ($course) {
                        return $course->with([
                            'uploader',
                            'class_categories'
                        ]);
                    }
                ]);
            }
        ])
            ->where('id', $user_id)
            ->first();

        return $this->sendResponse($user, 'Enrolled courses.', 200);
    }

    public function enrollCourse($user_id, Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        Enrollment::firstOrCreate([
            'user_id' => $user_id,
            'course_id' => $request->course_id
        ]);
        
        return $this->sendResponse([], 'Successfully enrolled.', 200);
    }

    public function enrollCourses($user_id, Request $request)
    {
        $request->validate([
            'course_id.*' => 'required|exists:courses,id'
        ]);

        $causer = User::find($this->authUser()->id);

        $user = User::find($user_id);

        $enrolled_courses = [];

        foreach ($request->course_id as $course_id) {
            $course = Course::find($course_id);

            if(!$course->price) {
                $course->price = 0;
            }

            if($course->price > 0) {

                $invoice = Invoice::where('course_id', $course_id)->where('user_id', $user_id)
                    ->where(function ($query) {
                        $query->where('status', Invoice::PAYMENT_PENDING)
                            ->orWhere('status', Invoice::PAYMENT_COMMITMENT_LETTER)
                            ->orWhere('status', Invoice::PAYMENT_SUCCESS);
                    })
                    ->first();

                if(!$invoice) {
                    $ref_id = self::refId();

                    $invoice = Invoice::create([
                        'reference_id' => $ref_id,
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'mode_of_payment' => Invoice::OFFLINE,
                        'amount' => $course->price,
                        'is_free' => $course->is_free,
                        'type_of_payment' => $request->type_of_payment,
                        'type_of_participant' => $request->type_of_participant,
                        'enroll_by_id' => $this->authUser()->id,
                        'status' => Invoice::PAYMENT_PENDING
                    ]);

                    $request->validate([
                        'prime_resolution' => 'nullable|mimes:pdf,doc,docx|max:10240',
                        'endorsement_letter' => 'nullable|mimes:pdf,doc,docx|max:10240',
                        'commitment_letter' => 'nullable|mimes:pdf,doc,docx|max:10240',
                    ], [
                        'prime_resolution.max' => 'The file must not be larger than 10MB.',
                        'endorsement_letter.max' => 'The file must not be larger than 10MB.',
                        'commitment_letter.max' => 'The file must not be larger than 10MB.',
                    ]);
                
                    $files = [
                        'endorsement_letter' => EndorsementLetter::class,
                        'prime_resolution' => PrimeResolution::class,
                        'commitment_letter' => CommitmentLetter::class
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
                                return $this->sendResponse([], 'An error occurred while uploading a requirement.', 500);
                            }
                        }
                    }
                }
                
            } else {

                $enrollment = Enrollment::where('user_id', $user_id)->where('course_id', $course_id)->exists();

                if ($enrollment) {
                    $enrolled_courses[] = $course->title ?? "Course ID $course_id";
                    continue;
                }

                Enrollment::firstOrCreate([
                    'user_id' => $user_id,
                    'course_id' => $course_id
                ]);

                $invoice = Invoice::where('user_id', $user_id)->where('course_id', $course_id)->exists();

                if(!$invoice) {

                    $ref_id = self::refId();

                    $invoice = Invoice::create([
                        'reference_id' => $ref_id,
                        'user_id' => $user_id,
                        'course_id' => $course_id,
                        'mode_of_payment' => Invoice::OFFLINE,
                        'status' => Invoice::PAYMENT_FREE,
                        'amount' => $course->price,
                        'is_free' => $course->is_free,
                        'type_of_payment' => $request->type_of_payment,
                        'type_of_participant' => $request->type_of_participant,
                    ]);

                }
    
                $course = Course::where('id', $course_id)->first();

                if(!$enrollment) {
                    activity('user_enrollment')
                        ->causedBy($causer)
                        ->withProperties([
                            'user_id' => $user_id,
                            'email' => $user->email,
                            'course_id' => $course_id,
                        ])
                        ->event('created')
                        ->performedOn($user)
                        ->log('User Enrolled');

                    $apiKey = config("services.sender.key");

                    if($apiKey) { 
                        SendSuccessfulEnrollmentEmail::dispatch([
                            'name' => $user->full_name,
                            'courses' => [$course],
                            'url' => StandaloneConfig::domainName(),
                            'tenant_id' => StandaloneConfig::tenantId(),
                            'email' => $user->email
                        ]);
                    } else {
                        Mail::to($user->email)->send(new CourseEnrollment([
                            'name' => $user->full_name,
                            'courses' => [$course_id],
                            'url' => StandaloneConfig::domainName(),
                            'tenant_id' => StandaloneConfig::tenantId()
                        ]));
                    }
                }
            }

        }

        $message = '';

        if (count($enrolled_courses)) {
            $message .= 'Already enrolled in: ' . implode(', ', $enrolled_courses) . '.';
        } else {
            $message .= 'Successfully enrolled.';
        }

        return $this->sendResponse([], trim($message), 200);

    }

    public function deleteEnrolledCourse($enrollment_id, Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id'
        ]);

        $enrollment = Enrollment::where('id', $enrollment_id)
            ->where('user_id', $request->user_id)
            ->where('course_id', $request->course_id)
            ->first(); 

        if (!$enrollment) {
            return $this->sendResponse([], 'Data not found.', 404);
        }

        $enrollment->delete();

        return $this->sendResponse([], 'Enrollment deleted.', 200);
    }

    public function paymentStatusCheck(Request $request)
    {

        $hash = md5($request->email . $request->amount . config('services.payment_gateway.secret'));

        $response = Http::asForm()->post(config('services.payment_gateway.url') . '/paymentStatusCheck', [
        'ref_id' => $request->ref_id,
        'hash' => $hash
        ]);
        
        Log::info("paymentStatusCheck - hash:" . $hash);
        Log::info("paymentStatusCheck - ref_id:" . $request->ref_id);
        Log::info("paymentStatusCheck - response:" . $response);

        if (isset($response['status'])) {

            $invoices = Invoice::where('reference_id', $response['ref_id']);
            if ($response['status'] == '1') {
                
                $invoices->update([
                    'status' => 1,
                    'data' => $response
                ]);

                foreach ($invoices->get() as $invoice) {
                    $enrollment = Enrollment::where('user_id', $invoice->user_id)->where('course_id', $invoice->course_id)->exists();

                    Enrollment::firstOrCreate([
                        'user_id' => $invoice->user_id,
                        'course_id' => $invoice->course_id
                    ]);
                    
                    $user = User::where('id', $invoice->user_id)->first();

                    if(!$enrollment) {
                        activity('user_enrollment')
                        ->causedBy($user)
                        ->withProperties([
                            'user_id' => $invoice->user_id,
                            'email' => $user->email,
                            'course_id' => $invoice->course_id,
                        ])
                        ->event('created')
                        ->performedOn($user)
                        ->log('User Enrolled');
                    }  
                }
            }
        }

        return $this->sendResponse($response->json(), 'Payment status.', 200);
    }

    public function exportCourseInvoices(Request $request)
    {
        
        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'invoice',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessAdminInvoicesListExport::dispatch(
            $export->id,
            $request->search,
            $request->type_of_learning,
            $request->status,
            $request->date_from,
            $request->date_to,
            $this->authUser()->id
        );
    
        return response()->json([
            'message' => 'Export has been queued',
            'status' => 'processing',
        ]);

    }
    
    public function exportInvoice(Request $request, $course_uuid) {
        
        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'invoice',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessAdminCourseInvoicesExport::dispatch(
            $export->id,
            $course_uuid,
            $request->invoice_status,
            $request->mode_of_payment,
            $request->type_of_payment,
            $request->date_from,
            $request->date_to,
            $request->search,
            $request->enrolled_by,
            $request->enrollment_status,
            $request->receipt_pick_up_option
        );
    
        return response()->json([
            'message' => 'Export has been queued',
            'status' => 'processing',
        ]);
    }

    public function importEnrollees(Request $request) {
        try {
            $request->validate([
                'file' => 'required|mimes:csv,txt', 
                'course_id' => 'required|exists:courses,id'
            ]);
            
            Log::info('1');
            Excel::import(new EnrolleesImport($request->course_id, $this->authUser()->id), $request->file('file'));
        
            return $this->sendResponse([], 'You have successfully enrolled learners.', 201);
        } catch (\Exception $e) {

            ImportLog::create([
              'user_id' => $this->authUser()->id,
              'status' => 'error',
              'type' => 'Registration Bulk Import',
              'message' => 'Something went wrong on importing: '. $e
            ]);
            
            Log::info('Bulk enroll error'. $e);
            throw $e;
        }
    }

    public function uploadProofOfPayment(Request $request) {

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'proof_of_payment' => 'required|mimes:pdf,docx,doc,png,jpg,jpeg|max:10240',
            'reference_number' => 'required|string'
        ], [
            'proof_of_payment.max' => 'The file must not be larger than 10MB.',
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)->first();

        $proof_of_payment = ProofOfPayment::create([
            'invoice_id' => $invoice->id,
            'reference_number' => $request->reference_number
        ]);

        $path = "proof_of_payment/{$proof_of_payment->id}";

        if (config('app.env') == 'prod') {

            $file_path = Storage::disk('s3')->put($path, $request['proof_of_payment']);

            $url = Storage::disk('s3')->url($file_path);
        } else {

            $file_path = Storage::disk('public')->put($path, $request['proof_of_payment']);

            $url = asset($file_path);
        }

        $proof_of_payment->update([
            'url' => $url
        ]);

        return $this->sendResponse([], 'Success!', 201);
    }

    public function uploadCommitmentLetter(Request $request) {

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'commitment_letter' => 'required|mimes:pdf,docx,doc|max:10240',
        ], [
            'commitment_letter.max' => 'The file must not be larger than 10MB.',
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)->first();

        $commitment_letter = CommitmentLetter::create([
            'invoice_id' => $invoice->id,
        ]);

        $path = "commitment_letter/{$commitment_letter->id}";

        if (config('app.env') == 'prod') {

            $file_path = Storage::disk('s3')->put($path, $request['commitment_letter']);

            $url = Storage::disk('s3')->url($file_path);
        } else {

            $file_path = Storage::disk('public')->put($path, $request['commitment_letter']);

            $url = asset($file_path);
        }

        $commitment_letter->update([
            'url' => $url
        ]);

        return $this->sendResponse([], 'Success!', 201);
    }

    public function uploadPrimeRequirements(Request $request) {

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'prime_resolution' => 'required|mimes:pdf,doc,docx|max:10240',
            'endorsement_letter' => 'required|mimes:pdf,doc,docx|max:10240',
        ], [
            'prime_resolution.max' => 'The file must not be larger than 10MB.',
            'endorsement_letter.max' => 'The file must not be larger than 10MB.',
        ]);

        $invoice = Invoice::where('id', $request->invoice_id)->first();
                
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
                    return $this->sendResponse([], 'An error occurred while uploading a requirement.', 500);
                }
            }
        }

        return $this->sendResponse([], 'Success!', 201);
    }

}
