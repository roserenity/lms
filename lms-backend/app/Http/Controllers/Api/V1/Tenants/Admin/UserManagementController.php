<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;

use App\Imports\Tenants\UsersImport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Requests\Tenants\UserAccountRequest;
use App\Http\Requests\Tenants\UserAccountStatusRequest;
use App\Http\Requests\Tenants\UserAccountRoleRequest;
use App\Mail\Tenants\Course\CourseEnrollment;
use App\Mail\Tenants\Auth\SuccessfulRegistration;

use App\Exports\Tenants\Admin\UserManagementExport;

use App\Models\ClassCategory;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\ImportLog;
use App\Models\CustomRegistrationPage;
use Carbon\Carbon;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Helpers\StandaloneConfig;
use App\Jobs\Tenants\ProcessAdminUserManagementExport;
use App\Jobs\Tenants\SendSuccessfulEnrollmentEmail;
use App\Jobs\Tenants\SendSuccessfulRegistrationEmail;
use App\Jobs\Tenants\SendUserManagementUserDeletionEmail;
use App\Models\Exports;
use Log;

use Excel;

class UserManagementController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.admin');
  }

  public function index(Request $request)
  {

    if ($request->paginate) {

      $searchParts = explode(' ', request('search'));
      $specified_params = ["page", "paginate", "search", "role[]", "role", "status", "created_at"];
      $other_payload = Helper::filterSpecifiedParams($request->all(), $specified_params);

      $users = User::when(count($other_payload) > 0, function($query) use($other_payload) {
          $pages = CustomRegistrationPage::select(['title', 'id'])->with(['custom_registration_fields' => function($innerquery) use($other_payload) {
            $innerquery->whereIn('input_field_name', $other_payload)->select(['input_field_name', 'id', 'custom_registration_page_id']);
          }])->get();

          foreach ($pages as $page) {        
            foreach ($page['custom_registration_fields'] as $field) {
              $query->when(request($field['input_field_name']), function($subquery) use($page, $field) {
                $nestedKey = 'data->' . $page['title'] . '->' . $field['input_field_name'];
                if(is_array(request($field['input_field_name']))) {
                  foreach (request($field['input_field_name']) as $value) {
                    $subquery->orWhereJsonContains($nestedKey, $value);
                  }
                } else {
                  $subquery->orWhereJsonContains($nestedKey, request($field['input_field_name']));
                }
              });
            }
          }
        })
        ->when(request('search'), function ($query) use ($searchParts) {
          Helper::queryNameEmailSearch($searchParts, $query);
        })->when(!empty(request('role')), function ($query) {

          if(is_array(request('role'))) {
            $query->whereIn('role', request('role'));
          } else {
            $query->where('role', request('role'));
          }
        })
        ->when(!empty(request('status')), function ($query) {
          $query->where('status', request('status'));
        })
        ->with(['enrollments' => function ($enrollments) {
            return $enrollments->has('course')->with([
                'course' => function ($course) {
                    return $course->with([
                        'uploader',
                        'class_categories'
                    ]);
                }
            ]);
        }])
        ->when(($request->created_from || $request->created_to), function ($query) use ($request) {
          FilterHelper::queryCreatedAt($query, $request->created_from, $request->created_to);
        })
        ->orderBy('created_at', 'desc')
        ->paginate($request->paginate);
    } else {
      $users = User::where('role', '!=', USER::ROLE_ADMIN)->get();
    }
    foreach ($users as $_user) {
      $_courses = Enrollment::where('user_id', $_user->id)->get();
      $_course_id = [];
      foreach ($_courses as $_course) {
        array_push($_course_id, $_course->course_id);
      }
      $_user->courses = $_course_id;
    }
    return $this->sendResponse($users, 'List of all users', 200);
  }

  public function create()
  {
    //
  }

  public function store(UserAccountRequest $request)
  {
    $user = User::create($request->validated());

    if ($request->role == User::ROLE_USER) {

      if (!empty($request->course_id)) {

        foreach ($request->course_id as $course) {
          $enrollment = Enrollment::where('user_id', $user->id)->where('course_id', $course)->exists();

          if(!$enrollment) {
            Enrollment::firstOrCreate([
              'user_id' => $user->id,
              'course_id' => $course
            ]);
  
            activity('user_enrollment')
              ->causedBy($this->authUser())
              ->withProperties([
                  'user_id' => $user->id,
                  'email' => $user->email,
                  'course_id' => $course->id,
              ])
              ->event('created')
              ->performedOn($user->id)
              ->log('User Enrolled');
          } 
        }

        $enrolled_courses = Course::whereIn('id', $request->course_id)->get();
        $random_courses = Course::where('status', Course::IS_PUBLISH)->inRandomOrder()->take(3)->get();

        $apiKey = config("services.sender.key");

        if($apiKey) {
          SendSuccessfulEnrollmentEmail::dispatch([
            'name' => $user->full_name,
            'courses' => $enrolled_courses,
            'url' => StandaloneConfig::domainName(),
            'tenant_id' => StandaloneConfig::tenantId(),
            'email' => $user->email
          ]);
        } else {
          Mail::to($user->email)->send(new CourseEnrollment([
            'name' => $user->full_name,
            'courses' => $enrolled_courses,
            'url' => StandaloneConfig::domainName(),
            'tenant_id' => StandaloneConfig::tenantId()
          ])); 
        }
      }
      
      $random_courses = Course::where('status', Course::IS_PUBLISH)->inRandomOrder()->take(3)->get();

      $apiKey = config("services.sender.key");

      if($apiKey) { 
        SendSuccessfulRegistrationEmail::dispatch([
          'name' => $user->full_name,
          'email' => $user->email,
          'password' => $request->password,
          'user_role' => $user->role,
          'courses' => $random_courses,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]);
      } else {
        Mail::to($user->email)->send(new SuccessfulRegistration([
          'name' => $user->full_name,
          'email' => $user->email,
          'password' => $request->password,
          'user_role' => $user->role,
          'courses' => $random_courses,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]));
      }
      
    } else {
      $apiKey = config("services.sender.key");

      if($apiKey) {
        SendSuccessfulRegistrationEmail::dispatch([
          'name' => $user->full_name,
          'email' => $user->email,
          'password' => $request->password,
          'user_role' => $user->role,
          'courses' => [],
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]);
      } else {
        Mail::to($user->email)->send(new SuccessfulRegistration([
          'name' => $user->full_name,
          'email' => $user->email,
          'password' => $request->password,
          'user_role' => $user->role,
          'courses' => [],
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]));
      }

    }

    // $user->sendEmailVerificationNotification();

    return $this->sendResponse($user, 'You have successfully added a new user.', 201);
  }

  public function show($id)
  {
    $user = User::find($id);

    if (!$user) {
      return $this->sendResponse([], 'No user found.', 404);
    }

    return $this->sendResponse($user, 'Show user details', 200);
  }

  public function edit($id)
  {
    //
  }

  public function update(UserAccountRequest $request, $user_id)
  {
    $user = User::find($user_id);

    if (!$user) {
      return $this->sendResponse([], 'No user found.', 404);
    }

    $user->update([
      'first_name' => $request->first_name,
      'middle_name' => $request->middle_name,
      'last_name' => $request->last_name,
      'suffix' => $request->suffix,
      'gender' => $request->gender,
      'data' => $request->data,
      'role' => $request->role
    ]);

    return $this->sendResponse($user, 'User details has been updated.', 200);
  }

  public function destroy($id)
  {
    // User::find($id)->forceDelete();

    $user = User::find($id);

    activity('user_deletion')
      ->causedBy($this->authUser())
      ->withProperties([
          'user_id' => $user->id,
          'email' => $user->email,
          'course_id' => null,
      ])
      ->event('deleted')
      ->performedOn($user)
      ->log('User Deletion');
 
    $receiver = config('mail.error_mail_receiver');

    $full_domain = request()->getSchemeAndHttpHost();
    $request_url = request()->fullUrl();

    SendUserManagementUserDeletionEmail::dispatch([
      'name' => $user->full_name,
      'email' => $user->email,
      'receiver' => $receiver,
      'id' => $user->id,
      'deleted_by' => $this->authUser()->full_name,
      'full_domain' => $full_domain,
      'request_url' => $request_url
    ]);

    $user->delete();

    return $this->sendResponse([], 'User deleted.', 200);
  }
  
  public function importUsers(Request $request)
  {
    try {
      $request->validate(['file' => 'required|mimes:csv,txt']);
      $causer = User::find($this->authUser()->id);
  
      Excel::import(new UsersImport($causer), $request->file('file'));
  
      return $this->sendResponse([], 'You have successfully imported new users.', 201);
    } catch (\Exception $e) {

      ImportLog::create([
        'user_id' => $this->authUser()->id,
        'status' => 'error',
        'type' => 'Registration Bulk Import',
        'message' => 'Something went wrong on importing: '. $e
      ]);

      Log::info('Bulk upload error'. $e);
      throw $e;
    }
  }

  public function updateUserStatus(UserAccountStatusRequest $request)
  {
    $user = User::where('id', $request->safe()->only('user_id'))->first();

    if (!$user) {
      return $this->sendResponse([], 'No user found.', 404);
    }

    $user->status = $request->status;

    $user->save();

    return $this->sendResponse($user, 'User status has been updated.', 200);
  }

  public function updateUserRole(UserAccountRoleRequest $request)
  {
    $user = User::where('id', $request->safe()->only('user_id'))->first();

    if (!$user) {
      return $this->sendResponse([], 'No user found.', 404);
    }

    $user->role = $request->role;

    $user->save();

    return $this->sendResponse([], 'User role has been updated.', 200);
  }

  public function updatePromptUpdateAll()
  {
    User::where('needs_info_update', 0)->update(['needs_info_update' => 1]);
    return $this->sendResponse([], 'Prompt flag has been set.', 200);
  }

  public function updatePromptUpdateUser($id)
  {
    User::where('id', $id)->update(['needs_info_update' => 1]);
    return $this->sendResponse([], 'Prompt flag has been set.', 200);
  }

  public function exportUsers(Request $request) {
    
    $specified_params = ["page", "paginate", "role[]", "role", "status", "created_to", "created_from"];

    $requests = Helper::filterSpecifiedParams($request->all(), $specified_params);
   
    $export = Exports::create([
        'user_id' => $this->authUser()->id,
        'type' => 'user_management',
        'status' => 'processing',
        'exported_at' => now(),
        'filename' => '',
        'path' => '',
        'url' => ''
    ]);

    ProcessAdminUserManagementExport::dispatch(
        $export->id,
        $request->role,
        $request->status, 
        $request->search, 
        $request->created_to, 
        $request->created_from, 
        $requests, 
        $request
    );

    return response()->json([
        'message' => 'Export has been queued',
        'status' => 'processing',
    ]);
    
  }

  public function importLogs(Request $request) {

    $importLog = ImportLog::where('user_id', $this->authUser()->id)->with('course')->latest()->paginate($request->paginate);

    return $this->sendResponse($importLog, 'List of all import logs.', 200);
  }

  public function deleteLogs($id) {

    $importLog = ImportLog::find($id)->delete();

    if (!$importLog) {
      return $this->sendResponse([], 'Log not found.', 404);
    }

    return $this->sendResponse([], 'Log deleted.', 200);

  }

  public function deleteAllLogs() {

    ImportLog::where('user_id', $this->authUser()->id)->delete();

    return $this->sendResponse([], 'Logs deleted.', 200);
    
  }
}
