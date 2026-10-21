<?php

namespace App\Http\Controllers\Api\V1\Central;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use App\Http\Requests\Central\CreateTenantRequest;
use App\Http\Requests\Central\UpdateTenantRequest;
use App\Http\Requests\Central\TenantStatusRequest;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Helpers\StandaloneConfig;
use App\Jobs\Tenants\SendContactEmail;
use App\Services\Central\TenantService;

use App\Mail\Tenants\Homepage\SendContactMail;
use App\Models\Advisory;
use App\Services\Tenants\ScormCloudService;

use App\Models\Central\Tenant;
use App\Models\Course;
use App\Models\ClassCategory;
use App\Models\CustomRegistrationPage;
use App\Models\Certificate;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\LandingPageAsset;
use App\Models\TrainingCredit;
use App\Models\Scorm;
use App\Models\ScormRegistration;
use App\Models\ScormRegistrationProgress;
use App\Models\ScormRegistrationInteraction;
use App\Models\StudentModuleProgress;

use Mail;

class TenantController extends Controller
{
  private $tenantService;

  private $paginate = 10;

  public function __construct(TenantService $tenantService)
  {
    $this->middleware('auth.super_admin')->except([
      'validDomains',
      'showCustomRegistrationPage',
      'showClassCategories',
      'showRandomClassCategories',
      'showTrainingCredits',
      'showTenantInfo',
      'allCourses',
      'courses',
      'showCourse',
      'showCertificate',
      'paymentStatusCheck',
      'requestGatewayOfficialReceipt',
      'callback',
      'sendContactMail',
      'showAvailbilityDates',
      'showGroupedCourses',
      'freeCourses',
      'scormCloudCourseUploadCallback',
      'scormCloudCourseRegistrationPostBack',
      'showAdvisories',
      'showBackgrounds'
    ]);
    $this->tenantService = $tenantService;
  }

  public function index(Request $request)
  {
    //View tenant
    $tenants = Tenant::when($request->general_education == 'true', function ($query) {
      $query->whereRelation('info', 'organization', 'general_education');
    })
      ->when($request->ngo == 'true', function ($query) {
        $query->orWhereRelation('info', 'organization', 'ngo');
      })
      ->when($request->corporate == 'true', function ($query) {
        $query->orWhereRelation('info', 'organization', 'corporate');
      })
      ->when($request->active == 'true', function ($query) {
        $query->where('status', 'active');
      })
      ->when($request->inactive == 'true', function ($query) {
        $query->where('status', 'inactive');
      })
      ->when($request->today == 'true', function ($query) {
        $query->whereDate('created_at', date('Y-m-d'));
      })
      ->when($request->this_week == 'true', function ($query) {
        $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
      })
      ->when($request->this_month == 'true', function ($query) {
        $query->whereMonth('created_at', Carbon::now()->month)
          ->whereYear('created_at', Carbon::now()->year);
      })
      ->when($request->this_year == 'true', function ($query) {
        $query->whereYear('created_at', Carbon::now()->year);
      })
      ->with(['info', 'domains'])
      ->latest()
      ->paginate($request->paginate ?? $this->paginate);
    return $this->sendResponse($tenants, 'List of all tenants and their infos', 200);
  }

  public function show($tenant_id)
  {
    $tenant = Tenant::with(['info', 'domains'])->find($tenant_id);

    if (!$tenant) {
      return $this->sendResponse([], 'No tenant found.', 404);
    }

    return $this->sendResponse($tenant, 'Show tenant with domain and information', 200);
  }

  //Create Tenant
  public function store(CreateTenantRequest $request)
  {
    $sub_domain = Helper::stripSubDomainName($request->sub_domain);

    $tenant_id = Helper::generateTenantId($sub_domain);

    $domain = $sub_domain . '.' . config('services.app_central');

    $tenant = Tenant::create($request->safe()->only(['tenant_name', 'tenant_description', 'domain_name', 'status']) + [
      'id' => $tenant_id,
      'api_endpoint' => $domain
    ]);

    $tenant->update([
      'navigation_background_color' => $request->navigation_background_color,
      'navigation_banner_color' => $request->navigation_banner_color,
      'navigation_link_color' => $request->navigation_link_color,
      'navigation_text_icon_color' => $request->navigation_text_icon_color,
      'navigation_link_inactive_color' => $request->navigation_link_inactive_color,
      'sidebar_color' => $request->sidebar_color,
      'mini_navigation_background_color' => $request->mini_navigation_background_color,
      'mini_navigation_link_color' => $request->mini_navigation_link_color,
      'mini_navigation_icon_color' => $request->mini_navigation_icon_color,
      'mini_navigation_inactive_link_color' => $request->mini_navigation_inactive_link_color,
      'primary_buttons_color' => $request->primary_buttons_color,
    ]);

    $tenant->domains()->create([
      'domain' => $domain
    ]);

    $tenant->info()->create($request->safe()->only(['contact_person', 'email', 'organization', 'mobile_no', 'tel_no']) + ['tenant_id' => $tenant->id]);

    if ($request->images) {
      foreach ($request->images as $key => $image) {
        $this->tenantService->uploadTenantLogo([
          'tenant_id' => $tenant_id,
          'image' => $image,
          'image_key' => $request->image_key[$key]
        ]);
      }
    }

    $result = $tenant->with(['info'])->get();

    if (!$tenant->passport_installed && $tenant->status === 'active') {

      $tenant->run(function () use ($tenant) {
        $this->tenantService->createTenantAdminAccount($tenant->info->email, $tenant->id);
        Artisan::call("tenants:run passport:client --option='personal=personal' --option='name={$tenant->id}' --tenants={$tenant->id}");
      });
    }

    return $this->sendResponse($result, 'You have successfully added a new tenant.', 201);
  }

  public function removeTenantPhoto(Request $request, $tenant_id)
  {
    $tenant = Tenant::find($tenant_id);

    if (!$tenant) {
      return $this->sendResponse([], 'No tenant found.', 404);
    }

    $tenant->images()->where('image_key', $request->image_key)->delete();

    return $this->sendResponse([], 'Tenant photo has been removed.', 200);
  }

  public function update(UpdateTenantRequest $request, $tenant_id)
  {
    $tenant = Tenant::find($tenant_id);

    if (!$tenant) {
      return $this->sendResponse([], 'No tenant found.', 404);
    }

    $request->validate([
      'sub_domain' => 'required|unique:tenants,api_endpoint,' . $tenant_id . '|max:255',
      'email' => 'required|email|unique:tenant_infos,email,' . $tenant->info->id . '|max:255',
    ]);

    $tenant->update(
      $request->safe()->only([
        'tenant_name',
        'domain_name',
        'status',
        'tenant_description'
      ])
    );

    $tenant->info()->update(
      $request->safe()->only([
        'organization',
        'contact_person',
        'mobile_no',
        'tel_no'
      ])
    );

    $tenant->update([
      'navigation_background_color' => $request->navigation_background_color,
      'navigation_banner_color' => $request->navigation_banner_color,
      'navigation_link_color' => $request->navigation_link_color,
      'navigation_text_icon_color' => $request->navigation_text_icon_color,
      'navigation_link_inactive_color' => $request->navigation_link_inactive_color,
      'sidebar_color' => $request->sidebar_color,
      'mini_navigation_background_color' => $request->mini_navigation_background_color,
      'mini_navigation_link_color' => $request->mini_navigation_link_color,
      'mini_navigation_icon_color' => $request->mini_navigation_icon_color,
      'mini_navigation_inactive_link_color' => $request->mini_navigation_inactive_link_color,
      'primary_buttons_color' => $request->primary_buttons_color,
    ]);

    if ($request->images) {
      foreach ($request->images as $key => $image) {
        $this->tenantService->updateTenantLogo([
          'tenant_id' => $tenant->id,
          'image_id' => $request->image_id[$key],
          'image' => $image,
          'image_key' => $request->image_key[$key]
        ]);
      }
    }

    $result = Tenant::with('info')->find($tenant_id);

    return $this->sendResponse($result, 'Tenant information has been updated.', 200);
  }

  public function destroy($tenant_id)
  {
    $tenant = Tenant::find($tenant_id);

    $tenant->delete();

    return $this->sendResponse($tenant, $tenant->tenant_name . ' tenant account successfully deleted.', 200);
  }

  // Update Tenant Status
  public function updateTenantStatus(TenantStatusRequest $request)
  {
    $result = $this->tenantService->updateTenantStatus($request->validated());

    $tenant = Tenant::where('id', $request->tenant_id)->first();

    if (!$tenant->passport_installed) {

      $tenant->run(function () use ($result, $tenant) {
        $this->tenantService->createTenantAdminAccount($tenant->info->email, $tenant->id);
        Artisan::call("tenants:run passport:client --option='personal=personal' --option='name={$tenant->id}' --tenants={$tenant->id}");
      });

      $result->update(['passport_installed' => true]);
    }

    return $this->sendResponse($tenant, 'Tenant status has been updated.', 200);
  }

  public function validDomains()
  {
    $domains = Tenant::where('status', 'active')->pluck('domain_name');

    return $this->sendResponse($domains, 'List of all valid domains.', 200);
  }

  public function showCustomRegistrationPage()
  {
    $custom_registration_pages = CustomRegistrationPage::all();

    return $this->sendResponse($custom_registration_pages, 'List of all custom registration page.', 200);
  }

  public function showClassCategories()
  {
    $class_categories = ClassCategory::all();

    return $this->sendResponse($class_categories, 'List of all class categories.', 200);
  }

  public function showRandomClassCategories()
  {
    $class_categories = ClassCategory::inRandomOrder()
      ->limit(3)
      ->with([
        'courses' => function ($courses) {
          return $courses->select(['id', 'title', 'uuid', 'available_until'])
            ->where('status', Course::IS_PUBLISH);
        }
      ])
      ->get();

    return $this->sendResponse($class_categories, 'List of all class categories.', 200);
  }

  public function showTenantInfo()
  {
    // Standalone installs only migrate the tenant schema, so there is no central tenants table.
    if (!Schema::hasTable('tenants')) {
      return $this->sendResponse([
        'info' => ['organization' => config('app.name')],
        'images' => [],
      ], 'information', 200);
    }

    $tenant =Tenant::select(['id', 'data'])->with(['info' => fn($info) => $info->select(['tenant_id', 'organization'])])->find(StandaloneConfig::tenantId());

    if (!$tenant) {
      return $this->sendResponse([], 'No tenant found.', 404);
    }

    $tenant->makeHidden(['tenancy_db_name', 'id']);

    if($tenant->info) 
    {
      $tenant->info->makeHidden(['tenant_id']);
    }

    return $this->sendResponse($tenant, 'information', 200);
  }

  public function allCourses(Request $request)
  {
    if($request->paginate) {

      $courses = Course::with([
        'class_categories'])
        ->select(['id', 'uuid', 'title', 'price', 'training_hours', 'available_until', 'is_free', 'type_of_learning', 'course_code'])
        ->where('status', Course::IS_PUBLISH)
        ->when($request->type_of_learning, function($query) use ($request){
          $query->where('type_of_learning', $request->type_of_learning);
        })
        ->when($request->search, function ($query) use ($request) {
          FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
        })
        ->when($request->course_name_sort, function ($query) use ($request) {
          $query->orderBy('title', $request->course_name_sort);
        })
        ->when(!empty(request('classes')), function ($query) use ($request) {
          $query->whereHas('class_categories', function ($subquery) use ($request) {
            $subquery->where('class_code', $request->classes);
          });
        })
        ->when($request->price_min, function ($query) use ($request) {
          $query->where('price', '>=', intval($request->price_min));
        })
        ->when($request->price_max, function ($query) use ($request) {
            $query->where('price', '<=', intval($request->price_max));
        })
        ->withCount(['modules', 'zoom_meetings'])
        ->latest('updated_at')
        ->paginate($request->paginate);
  
      $courses->makeHidden([
        'course_card',
        'total_done_assessments',
        'total_done_topics',
        'total_done_sub_topics',
        'total_done_course_evaluations',
        'total_done_module_evaluations',
        'total_attended_zoom_meetings',
        'total_done_group_activities',
        'total_time_spent',
        'assessments'
      ]);
    } else {
      $courses = Course::select(['id', 'uuid', 'title', 'type_of_learning', 'status', 'available_until', 'price', 'is_free'])
          ->where('status', Course::IS_PUBLISH)
          ->get();

        $courses->makeHidden([
          'course_card',
          'total_done_assessments',
          'total_done_topics',
          'total_done_sub_topics',
          'total_done_course_evaluations',
          'total_done_module_evaluations',
          'total_attended_zoom_meetings',
          'total_done_group_activities',
          'total_time_spent',
          'assessments',
          'user_is_enrolled'
        ]);
    }

    return $this->sendResponse($courses, 'List of courses', 200);
  }

  public function freeCourses(Request $request)
  {
    $currentDate = Carbon::now('Asia/Manila')->format('Y-m-d');
    $courses = Course::select(['id', 'uuid', 'title', 'type_of_learning', 'status', 'available_until', 'is_free', 'training_hours'])
      ->withCount('modules')
      ->when($request->search, function ($query) use ($request) {
          $query->where('title', 'LIKE', '%' . $request->search . '%');
      })
      ->where('status', Course::IS_PUBLISH)
      ->where('is_free', true)
      ->get();
      
    $courses->makeHidden([
      'course_card',
      'total_done_assessments',
      'total_done_topics',
      'total_done_sub_topics',
      'total_done_course_evaluations',
      'total_done_module_evaluations',
      'total_attended_zoom_meetings',
      'total_done_group_activities',
      'total_time_spent',
      'assessments',
      'user_is_enrolled'
    ]);
    return $this->sendResponse($courses, 'List of currently open free course', 200);
  }

  public function courses(Request $request)
  {

    $courses = Course::select(['id', 'uuid', 'title', 'type_of_learning', 'status', 'available_until', 'price', 'is_free'])
      ->where('status', '!=', Course::IS_DRAFT)
      ->get();

    $courses->makeHidden([
      'course_card',
      'total_done_assessments',
      'total_done_topics',
      'total_done_sub_topics',
      'total_done_course_evaluations',
      'total_done_module_evaluations',
      'total_attended_zoom_meetings',
      'total_done_group_activities',
      'total_time_spent',
      'assessments',
      'user_is_enrolled'
    ]);

    return $this->sendResponse($courses, 'List of courses', 200);
  }

  public function showCourse($course_uuid)
  {

    $selected = Course::where('uuid', $course_uuid)->first();

    if(!$selected) {
      return $this->sendResponse([], 'Course not found', 404);
    }

    $course = Course::with([
      'modules' => function ($modules) {
        return $modules->selectAttributes();
        },
      'uploader'
    ])
    ->where('uuid', $course_uuid)
    ->first()
    ->makeHidden([
      'course_card',
      'total_done_assessments',
      'total_done_topics',
      'total_done_sub_topics',
      'total_done_course_evaluations',
      'total_done_module_evaluations',
      'total_attended_zoom_meetings',
      'total_done_group_activities',
      'total_time_spent'
    ]);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    return $this->sendResponse($course, 'Course details.', 200);
  }

  public function showCertificate($certificate_uuid)
  {
    $certificate = Certificate::with(['user:id,first_name,middle_name,last_name,suffix', 'course:id,title', 'image'])
      ->where('uuid', $certificate_uuid)
      ->first();

    if (!$certificate) {
      return $this->sendResponse([], 'No certificate found.', 404);
    }

    return $this->sendResponse($certificate, 'View certificate', 200);
  }

  public function paymentStatusCheck(Request $request)
  {
    

    $hash = md5($request->email . $request->amount . config('services.payment_gateway.secret'));

    $response = Http::asForm()->post(config('services.payment_gateway.url') . '/paymentStatusCheck', [
      'ref_id' => $request->ref_id,
      'hash' => $hash
    ]);

    Log::info('payment status: ' . json_encode($response));

    if (isset($response['result']) != 'error') {
      $invoices = Invoice::where('reference_id', $response['ref_id']);
      $invoices->update([
        'status' => $response['status'],
      ]);
      if ($response['status'] == '1') {
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

  public function requestGatewayOfficialReceipt(Request $request)
  {
    $request->validate([
      'user_id' => 'required|exists:users,id',
      'course_uuid' => 'required|exists:courses,uuid',
      'reference_id' => 'required|exists:invoices,reference_id',
      'email' => 'required|email|exists:users,email'
    ]);

    $course = Course::where('uuid', $request->course_uuid)->first();

    $invoice = Invoice::where('course_id', $course->id)
    ->where('user_id', $request->user_id)
    ->where('reference_id', $request->reference_id)
    ->where('mode_of_payment', Invoice::ONLINE)
    ->where('status', Invoice::PAYMENT_SUCCESS)
    ->first();

    if(!$invoice) {
      return $this->sendResponse([], 'Official Receipt not available.', 404);
    }

    $hash = md5($request->email . $invoice->amount . config('services.payment_gateway.secret'));
    $response = Http::asForm()->post(config('services.payment_gateway.url') . '/getReceipt', [
      'gateway_ref_id' => $invoice->data['gateway_ref_id'] ?? NULL,
      'ref_id' => $invoice->data['ref_id'] ?? NULL,
      'hash' => $hash,
    ]);

    Log::info('payment-gateway/getReceipt/response: ' . $response);

    try {
      return response($response->body())
          ->header('Content-Type', 'application/pdf')
          ->header('Content-Disposition', 'attachment;');
    } catch (\Exception $e) {
      return $this->sendResponse([], 'Unexpected response.', 422);
    }

  }

  public function callback(Request $request)
  {

    // if (StandaloneConfig::tenantId() == 'default') {
    $invoices = Invoice::where('reference_id', $request->ref_id);
    $invoices->update([
      'status' => $request->status,
      'data' => $request->all()
    ]);

    //Log::info('callback: ' . $request);
    if ($request->status == '1') {
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
    $invoice = $invoices->first();
    $courses = Course::whereHas('invoices', function ($query) use ($request) {
      $query->where('reference_id', $request->ref_id);
    })
      ->get()->pluck('title')->toArray();
    $user = User::whereHas('invoices', function ($query) use ($request) {
      $query->where('reference_id', $request->ref_id);
    })
      ->first();
    $amount = Course::whereIn('id', $invoices->pluck('course_id'))
      ->where('is_free', false)
      ->sum('price');
    $course_list = implode(', ', $courses);
    $gateway_ref_id = $invoice->data['gateway_ref_id'];
    $ref_id = $request->ref_id;
    $hash = md5($user->email . $amount . config('services.payment_gateway.secret'));
    $back_url = StandaloneConfig::domainName() . '/dashboard';
    return view('successful_payments.tenants.default.success', compact('gateway_ref_id', 'ref_id', 'hash', 'course_list', 'back_url'));
    // } else {
    //   return response()->json('No callback functions found.', 404);
    // }
  }

  public function sendContactMail(Request $request)
  {
    $request->validate([
      'name' => 'required|max:255',
      'email' => 'required|email|max:255',
      'subject' => 'required|max:255',
      'message' => 'required',
    ]);

    $receiver = config('mail.contact_mail_receiver');

    $data = [
      'name' => $request->name,
      'email' => $request->email,
      'subject' => $request->subject,
      'message' => $request->message,
      'receiver' => $receiver
    ];

    $apiKey = config("services.sender.key");

    if($apiKey) {
      try {
        SendContactEmail::dispatch($data);
      } catch (\Exception $e) {
        Log::error('contact mail error' . $e);
        return $this->sendResponse([], 'Something went wrong, please try again later', 500);
      }
    } else {
      Mail::to($receiver)
        ->cc($request->email)
        ->send(new SendContactMail($data));
    }
  }

  public function showAvailbilityDates()
  {
    // $uniqueDates = Course::select('available_until')
    //   ->where('is_free', false)
    //   ->where('status', Course::IS_PUBLISH)
    //   ->pluck('available_until')
    //   ->map(function ($item) {
    //       return Carbon::parse($item['start_date'])->format('Y-m');
    //   })
    //   ->unique()
    //   ->toArray();

    $uniqueDates = Course::select(['available_until', 'is_free', 'status'])
      ->where('is_free', false)
      ->where('status', Course::IS_PUBLISH)
      ->whereNotNull('available_until')
      ->latest()
      ->get()
      ->flatMap(function ($course) {
        $startDate = Carbon::parse($course->available_until['start_date']);
        $endDate = Carbon::parse($course->available_until['end_date']);

        $startDate->startOfMonth();

        $period = $startDate->toPeriod(
            $endDate->endOfMonth(),
            '1 month'
        );
        
        $formattedDates = [];

        // Iterate through each date in the period and format it
        foreach ($period as $date) {
            $formattedDates[] = $date->format('Y-m');
        }

        return $formattedDates;
      })
      ->unique() // Ensure unique month-year combinations
      ->sort()
      ->values() // Re-index the collection
      ->toArray();

    return $this->sendResponse($uniqueDates, 'List of unique availability dates', 200);
  }

  public function showGroupedCourses(Request $request)
  {
    
    $startOfMonth = '';
    $endOfMonth = '';

    if ($request->date !== 'all') {
      if($request->date) {
        try {
          [$year, $month] = explode('-', $request->date);
    
          $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
          $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        } catch (\Exception $e) {
          $startOfMonth = '';
          $endOfMonth = '';
        }
      }
    }
    
    $courses = Course::select(['id', 'uuid', 'title', 'price', 'training_hours', 'available_until', 'is_free', 'type_of_learning'])
      ->when($request->search, function ($query) use ($request) {
          $query->where('title', 'LIKE', '%' . $request->search. '%');
      })
      ->when($request->type_of_learning, function ($query) use ($request) {
        $query->where('type_of_learning', $request->type_of_learning );
      })
      ->when($request->training_credits, function ($query) use ($request) {
        $query->where('training_credits', $request->training_credits );
      })
      ->when($request->date && $request->date !== 'all', function ($query) use ($startOfMonth, $endOfMonth) {
          $query->where(function ($query) use ($startOfMonth, $endOfMonth) {
              $query->where(function ($query) use ($startOfMonth, $endOfMonth) {
                  // Check when both start_date and end_date are present
                  $query->whereNotNull('available_until->start_date')
                        ->whereNotNull('available_until->end_date')
                        ->where('available_until->start_date', '<=', $endOfMonth)
                        ->where('available_until->end_date', '>=', $startOfMonth);
              })
              ->orWhere(function ($query) use ($startOfMonth, $endOfMonth) {
                  // Check when only start_date is present
                  $query->whereNotNull('available_until->start_date')
                        ->whereNull('available_until->end_date')
                        ->where('available_until->start_date', '<=', $endOfMonth);
              })
              ->orWhere(function ($query) use ($startOfMonth, $endOfMonth) {
                  // Check when only end_date is present
                  $query->whereNull('available_until->start_date')
                        ->whereNotNull('available_until->end_date')
                        ->where('available_until->end_date', '>=', $startOfMonth);
              });
          });
      })
      ->withCount(['modules', 'zoom_meetings'])
      ->where('is_free', false)
      ->where('status', Course::IS_PUBLISH)
      ->latest()
      ->paginate($request->paginate ?? $this->paginate);

    return $this->sendResponse($courses, 'List of courses', 200);
  }

  public function scormCloudCourseUploadCallback(Request $request)
  {
    Log::info('Request Data scormCloudCourseUploadCallback:', $request->all());

    $scorm = Scorm::where('job_id', $request->jobId)->first();

    $errors = [];

    $scormCloudService = app(ScormCloudService::class);

    try {
      if(Scorm::COMPLETED === $request->status) {
        $setCourseTitleResult = $scormCloudService->setCourseTitle($scorm->scorm_cloud_course_id, $scorm->title);
        if ($setCourseTitleResult['result'] !== 'success') {
          $errors[] = "Failed to set course title: " . json_encode($setCourseTitleResult['message']);
        }
  
        // Configure the course
        $setCourseConfigurationResult = $scormCloudService->setCourseConfiguration($scorm->scorm_cloud_course_id);
        if ($setCourseConfigurationResult['result'] !== 'success') {
          $errors[] = "Failed to set course configuration: " . json_encode($setCourseConfigurationResult['message']);
        }
  
        // Create a preview link
        $createPreviewLinkResult = $scormCloudService->createPreviewLink($scorm->scorm_cloud_course_id);
        if ($createPreviewLinkResult['result'] !== 'success') {
          $errors[] = "Failed to create preview link: " . json_encode($createPreviewLinkResult['message']);
        }
  
      }
    } catch (\Exception $e) {
      Log::error('Failed to update course:'. $e);
      $errors[] = "Failed to update course: " . $e;
    }

    if($scorm) {
      $scorm->update([
        'status' => $request->status,
        'data' => $request->all(),
        'errors' => $errors
      ]);
  
      $scorm->save();
    }

    return $this->sendResponse([], 'Callback received', 200);
  }

  public function scormCloudCourseRegistrationPostBack(Request $request)
  {

    try {

      $scorm_registration = ScormRegistration::where('registration_id', $request->id)->first();
        
      $registration_progress_encoded = json_encode($request->all());
      $registration_progress_activity_details_encoded = json_encode($request->activityDetails);
  
      $scorm_registration_progress = ScormRegistrationProgress::updateOrCreate([
          'scorm_registration_id' => $scorm_registration->id,
          'user_id' => $request->learner["id"]
      ], [
          'completion_status' => $request->registrationCompletion,
          'success_status' => $request->registrationSuccess,
          'completion_date' => $request->registrationCompletion == ScormRegistrationProgress::IS_COMPLETED ? $request->completedDate : null,
          'total_seconds_tracked' => $request->totalSecondsTracked,
          'first_accessed_date' => $request->firstAccessDate ?? null,
          'last_accessed_date' => $request->lastAccessDate ?? null,
          'activity_details' => $registration_progress_activity_details_encoded ?? null,
          'data' => $registration_progress_encoded,
      ]);
  
      $scorm_registration->update([
          'scorm_registration_progress_id' => $scorm_registration_progress['id']
      ]);
      
      $scorm_registration->save();

      foreach($request->activityDetails['children'] as $scorm_children) {

        Log::info(json_encode($scorm_children));

        foreach($scorm_children['runtime']['runtimeInteractions'] as $runtime) {
          $runtime_interactions_encoded = json_encode($runtime);

          $type = (substr($runtime['id'], 0, strlen('RISESTORYLINE')) === 'RISESTORYLINE' || substr($runtime['id'], 0, strlen('STORYLINE360')) === 'STORYLINE360') ? 'RISESTORYLINE' : 'RISE';

          if(substr($runtime['id'], 0, strlen('Scene')) === 'Scene') {
            $type = 'STORYLINE360';
          }

          //catch storyline multiple attempts

          // $interaction_count = ScormRegistrationInteraction::where('scorm_registration_id', $scorm_registration->id)
          //       ->where('interaction_id', '%'.str_replace(' ', '_', $runtime['id']).'%')
          //       ->where('user_id', $request->learner["id"])->count();

          if($type === 'STORYLINE') {
              $runtime['id'] = str_replace(' ', '_', $runtime['id'].'__0'); //append attempt count to replicate rise 
          }

          if($type === 'RISE') {
            ScormRegistrationInteraction::updateOrCreate([
                'scorm_registration_id' => $scorm_registration->id,
                'user_id' => $request->learner["id"],
                'type' => strtoupper($type),
                'interaction_id' => $runtime['id']
            ], [
                'interaction_type' => $runtime['type'] ?? 'LONGFILLIN',
                'interaction_timestamp' => $runtime['timestamp'],
                'correct_responses' => json_encode($runtime['correctResponses']),
                'point_value' => $runtime['weighting'],
                'learner_response' => $runtime['learnerResponse'] ?? '',
                'result' => $runtime['result'],
                'latency' => $runtime['latency'],
                'objectives' => json_encode($runtime['objectives']),
                'data' => $runtime_interactions_encoded,
            ]);
          }
        }

        foreach($scorm_children['runtime']['learnerComments'] as $comment) {

          $submissions = json_decode($comment["value"]);

          ScormRegistrationInteraction::updateOrCreate([
              'scorm_registration_id' => $scorm_registration->id,
              'user_id' => $request->learner["id"],
              'type' => $submissions->type,
              'interaction_id' => $submissions->question.'__0',
          ],[
              'interaction_type' => $submissions->question_type,
              'interaction_timestamp' => $submissions->date_submitted,
              'learner_response' => $submissions->answer ?? '',
              'result' => $submissions->result ?? NULL,
              'data' => $comment["value"],
          ]);

        }
          
      }

      if($request->registrationCompletion === 'COMPLETED') {
        StudentModuleProgress::updateOrCreate([
          'model_id' => $scorm_registration->scorm->model_id,
          'model_type' => $scorm_registration->scorm->model_type,
          'course_id' => $scorm_registration->scorm->course_id,
          'user_id' => $request->learner["id"]
        ],
        [
          'status' => StudentModuleProgress::STATUS_DONE,
        ]);
      }

      Log::info('scormCloudCourseRegistration saved');
    } catch (\Exception $e) {
      Log::info('Failed scormCloudCourseRegistrationPostBack:'. $e);
    }

    return $this->sendResponse([], 'Callback receiveddd', 200);
  }

  public function showAdvisories()
  {
    $advisories = Advisory::select(['id', 'created_at'])->with('image')->where('active', Advisory::IS_ACTIVE)->orderBy('sort_no')->get();

    return $this->sendResponse($advisories, 'List of all advisories.', 200);
  }

  public function showTrainingCredits()
  {
    $training_credits = TrainingCredit::select(['id', 'name'])->get();

    return $this->sendResponse($training_credits, 'List of all training credits.', 200);

  }

  public function showBackgrounds()
  {
    $backgrounds = LandingPageAsset::select(['id', 'asset_key'])->whereHas('image')->with('image')->where('asset_key', LandingPageAsset::ASSET_BG)->orderBy('sort_no')->first();

    return $this->sendResponse($backgrounds, 'List of all landing page backgrounds.', 200);
  }

}
