<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Exports\Tenants\Teacher\CourseEnrolleesProgressExport;
use App\Exports\Tenants\Teacher\CourseInvoicesExport;
use App\Exports\Tenants\Teacher\InvoiceExport;
use Illuminate\Support\Facades\Http;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Helpers\StandaloneConfig;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Tenants\Teacher\UserManualEnrollmentResource;
use App\Http\Requests\Tenants\InvoiceRequest;
use App\Jobs\Tenants\ProcessInstructorCourseInvoiceListExport;
use App\Jobs\Tenants\ProcessInstructorInvoicesListExport;
use App\Jobs\Tenants\SendSuccessfulEnrollmentEmail;
use App\Mail\Tenants\Course\CourseEnrollment;
use App\Models\CommitmentLetter;
use App\Services\Tenants\ImageService;
use App\Models\Course;
use App\Models\EndorsementLetter;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Exports;
use App\Models\NominationForm;
use App\Models\PrimeResolution;
use App\Models\ProofOfPayment;
use App\Services\Tenants\CheckoutService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

use Storage;

class EnrollmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
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

    public function index()
    {
        $users = User::has('invoices')
            ->whereHas('invoices', function ($query) {
                $query->where('enroll_by_id', $this->authUser()->id);
            })
            ->withCount('invoices as courses_count')
            ->get();
        return $this->sendResponse(UserManualEnrollmentResource::collection($users), 'List of offline manual enrollment', 200);
    }

    public function store(InvoiceRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        $course = Course::where('id', $request->course_id)->first();

        $invoice = Invoice::where('course_id', $course->id)->where('user_id', $user->id)->where('status', Invoice::PAYMENT_PENDING)->first();

        if($invoice) {
            return $this->sendResponse([], 'This learner has a pending transaction with '. $course->title.', please check with the admin.', 409);
        }
        
        $invoice = Invoice::where('course_id', $course->id)->where('user_id', $user->id)->where('status', Invoice::PAYMENT_COMMITMENT_LETTER)->first();

        if($invoice) {
            return $this->sendResponse([], 'This learner has submitted a commitment letter for '. $course->title.', please check with the admin.', 409);
        }

        $invoice = Invoice::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->where('status', Invoice::PAYMENT_SUCCESS)
                    ->where('status', Invoice::PAYMENT_FREE);
            })->first();

        if($invoice) {
            return $this->sendResponse([], 'This learner is already enrolled in '. $course->title.'.', 409);
        }

        if($course->price > 0) {

            $ref_id = self::refId();

            $invoice = Invoice::create($request->safe()->except(['email', 'mode_of_payment', 'course_id']) + [
                'reference_id' => $ref_id,
                'user_id' => $user->id,
                'course_id' => $request->course_id,
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

        } else {

            $enrollment = Enrollment::where('user_id', $user->id)->where('course_id', $request->course_id)->exists();

            Enrollment::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $request->course_id
            ]);

            $invoice = Invoice::where('user_id', $user->id)->where('course_id', $request->course_id)->exists();

            if(!$invoice) {

                $ref_id = self::refId();
                $invoice = Invoice::create([
                    'reference_id' => $ref_id,
                    'user_id' => $user->id,
                    'course_id' => $request->course_id,
                    'mode_of_payment' => Invoice::OFFLINE,
                    'status' => Invoice::PAYMENT_FREE,
                    'amount' => $course->price,
                    'is_free' => $course->is_free,
                    'type_of_payment' => $request->type_of_payment,
                    'type_of_participant' => $request->type_of_participant,
                ]);

            }

            if(!$enrollment) {
                $causer = User::find($this->authUser()->id);
                
                activity('user_enrollment')
                    ->causedBy($causer)
                    ->withProperties([
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'course_id' => $request->course_id,
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
                        'courses' => [$request->course_id],
                        'url' => StandaloneConfig::domainName(),
                        'tenant_id' => StandaloneConfig::tenantId()
                    ]));
                }
            }
        }
        
        // ImageService::uploadOfficialReceipt([
        //     'model_id' => $invoice->id,
        //     'model_type' => 'App\\Models\\Invoice',
        //     'image' => $request->file('image')
        // ]);

        return $this->sendResponse($user, 'Success!', 201);
    }

    public function showUser($user_id)
    {
        $user = User::with([
            'invoices' => function ($invoices) {
                return $invoices->with(['course', 'image', 'enroll_by'])->where('enroll_by_id', $this->authUser()->id)->orderBy('updated_at', 'desc');
            }
        ])
            ->where('id', $user_id)
            ->first();

        if (!$user) {
            return $this->sendResponse([], 'No user found.', 404);
        }

        return $this->sendResponse($user, 'User enrollment details.', 200);
    }

    public function showCourses(Request $request)
    {

        $courses = Course::select(['title', 'uploader_id', 'uuid', 'status', 'type_of_learning', 'available_until', 'course_code'])
            ->with(['shared_to_users'])
            ->when($request->search, function ($query) use ($request) {
                FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->whereIn('status', $request->status ? [$request->status] : [ Course::IS_PUBLISH, COURSE::IS_UNPUBLISH ]);
            })
            ->when($request->type_of_learning, function ($query) use ($request) {
                $query->where('type_of_learning', $request->type_of_learning);
            })
            ->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                FilterHelper::queryAvailabilityDates($query, $request->date_from, $request->date_to);
            })
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('uploader_id', $this->authUser()->id)
                    ->orWhereRelation('shared_to_users', 'user_id', $this->authUser()->id);
                });
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
            ->latest()
            ->paginate($request->paginate);

        return $this->sendResponse($courses, 'List of all courses.', 200);
    }

    public function showCourseEnrollees(Request $request, $course_uuid)
    {
        $course = Course::where('uuid', $course_uuid)->first();

        if (!$course) {
            return $this->sendResponse([], 'No course found.', 404);
        }

        $searchParts = explode(' ', $request->search);
        $searchParts2 = explode(' ', $request->enrolled_by);

        $invoices = Invoice::with([
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
            ->when($request->search, function ($query) use ($request, $searchParts) {
                $query->whereHas('enrollee', function ($innerQuery) use ($request, $searchParts) {
                    Helper::queryNameEmailSearch($searchParts, $innerQuery);
                });
            })
            ->when($request->enrolled_by, function ($query) use ($request, $searchParts2) {
                $query->whereHas('enroll_by', function ($innerQuery) use ($request, $searchParts2) {
                    Helper::queryNameSearch($searchParts2, $innerQuery);
                });
            })
            ->when(($request->date_from || $request->date_to), function ($query) {
              FilterHelper::queryCreatedAt($query, request('date_from'), request('date_to'));
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
            ->whereHas('enrollee')
            ->paginate($request->paginate);

        return $this->sendResponse([ 'title' => $course->title, 'invoices' => $invoices], 'List of all invoices.', 200);
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
    
        ProcessInstructorInvoicesListExport::dispatch(
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
    
    public function exportInvoice(Request $request, $course_uuid)
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
    
        ProcessInstructorCourseInvoiceListExport::dispatch(
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

    public function uploadProofOfPayment(Request $request) {

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'proof_of_payment' => 'required|mimes:pdf,docx,doc,png,jpg,jpeg',
            'reference_number' => 'required|string'
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
            'commitment_letter' => 'required|mimes:pdf,docx,doc'
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
