<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\StandaloneConfig;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Tenants\CourseRequest;
use App\Http\Requests\Tenants\UpdateCourseStatusRequest;

use App\Services\Tenants\ImageService;
use App\Services\Tenants\ScormCloudService;
use App\Models\Course;
use App\Models\CourseEvaluation;
use App\Models\User;
use App\Models\CourseCertificateTemplate;

use App\Helpers\FilterHelper;
use App\Helpers\S3Helper;
use App\Jobs\Tenants\SendCourseInvitationEditorEmail;
use App\Jobs\Tenants\SendCourseInvitationViewerEmail;
use App\Mail\Tenants\Course\CourseInvitationEditor;
use App\Mail\Tenants\Course\CourseInvitationViewer;
use App\Models\GroupChat;
use App\Models\Enrollment;
use App\Models\AssessmentAnswer;
use App\Models\ModuleContent;
use App\Models\Scorm;
use App\Models\SubTopic;
use App\Models\Topic;
use App\Services\Tenants\GoogleDriveService;
use App\Models\TrainingCredit;
use Log;
use Mail;

use App\Notifications\GroupMessageNotification;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.instructor');
  }

  public function index()
  {
    $courses = Course::when('status', function ($query) {
      return request('status') ? $query->where('status', request('status')) : $query;
    })
      ->with([
        'class_categories',
        'shared_to_users',
        'my_permission',
      ])
      ->with(['modules' => function ($query) {
        $query->with([
          'topics' => function ($query) {
            $query->with([
              'topic_has_assessments',
              'sub_topics' => function ($query) {
                $query->with([
                  'sub_topic_has_assessments',
                ])->orderBy('sort_no', 'ASC');
              }
            ])->orderBy('sort_no', 'ASC');
          }
        ]);
      }])
      ->where(function ($query) {
        $query->where(function ($query) {
            $query->where('uploader_id', $this->authUser()->id)
            ->orWhereRelation('shared_to_users', 'user_id', $this->authUser()->id);
        });
      })
      ->get()
      ->makeHidden([
        'total_attended_zoom_meetings',
        'total_done_assessments',
        'total_done_course_evaluations',
        'total_done_module_evaluations',
        'total_done_group_activities',
        'total_done_sub_topics',
        'total_done_topics',
        'total_time_spent'
      ]);

    return $this->sendResponse($courses, 'List of all courses.', 200);
  }

  public function create()
  {
    //
  }

  public function store(CourseRequest $request)
  {
    $course = Course::create([...$request->validated(), 'is_free' => true, 'price' => 0 ]);

    return $this->sendResponse($course, 'You have successfully added a new course.', 201);
  }


  public function updateCourseUpdatedAt($course_id)
  {
    $course = Course::find($course_id);

    $course->touch();

    return $this->sendResponse($course, 'You have updated a course.', 201);
  }

  public function show($id)
  {
    $course = Course::with([
      'modules' => function ($query) {
        $_modules = $query->with([
          'module_content' => function ($query) {
            $query->with([
                'contentable' => function ($morphTo) {
                    $morphTo->morphWith([
                        \App\Models\Topic::class => [
                            'scorm',
                            'topic_has_assessments' => function ($query) {
                              $query->with(['assessment_questions' => function ($subquery) {
                                $subquery->with('assessment_answer');
                              }]);
                            },
                            'learning_material',
                            'sub_topics' => function ($query) {
                                $query->with([
                                    'scorm',
                                    'sub_topic_has_assessments' => function ($query) {
                                      $query->with(['assessment_questions' => function ($subquery) {
                                        $subquery->with('assessment_answer');
                                      }]);
                                    },
                                    'learning_material'
                                ])->orderBy('sort_no', 'ASC');
                            }
                        ],
                        \App\Models\Assessment::class => ['assessment_questions' => function ($subquery) {
                          $subquery->with('assessment_answer');
                        }] // if you want to load any relationships here
                    ]);
                }
            ]);
          },
          'prerequisites',
          // 'topics' => function ($query) {
          //   $query->with([
          //     'scorm',
          //     'topic_has_assessments',
          //     'learning_material',
          //     'sub_topics' => function ($query) {
          //       $query->with([
          //         'scorm',
          //         'sub_topic_has_assessments',
          //         'learning_material'
          //       ])->orderBy('sort_no', 'ASC');
          //     }
          //   ])->orderBy('sort_no', 'ASC');
          // },
          'module_evaluations'
        ])->get();
      },
      'enrollments' => function ($enrollments) {
        return $enrollments->with([
          'user'
        ])->get();
      },
      'course_evaluations' => function ($course_evaluations) {
        return $course_evaluations->with([
          'course_evaluation_questions' => function ($course_evaluation_questions) {
            $course_evaluation_questions->with('image');
          }
        ]);
      },
      'shared_to_users', 'my_permission', 'class_categories', 'certificate_template', 'training_credit'
    ])->find($id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $course->makeHidden([
      'total_attended_zoom_meetings',
      'total_done_assessments',
      'total_done_course_evaluations',
      'total_done_group_activities',
      'total_done_module_evaluations',
      'total_done_sub_topics',
      'total_done_topics',
      'total_time_spent'
    ]);


    return $this->sendResponse($course, 'Show course details.', 200);
  }

  public function edit($id)
  {
    //
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'title' => 'required|max:255',
      'course_code' => 'sometimes|max:25',
      'description' => 'nullable',
      'price' => 'nullable|numeric',
      'is_free' => 'nullable|boolean',
      'start_date' => 'required_with:end_date|nullable|date',
      'end_date' => 'required_with:start_date|nullable|date',
      'training_hours' => 'required|numeric|min:1',
      'credit_training_hours' => 'required|numeric|min:1',
      'training_credits' => 'required|max:255',
      'facilitator' => 'nullable|max:255'
    ]);

    $course = Course::find($id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $course->title = $request->title;
    $course->course_code = $request->course_code;
    $course->description = $request->description;
    $course->price = $request->price;
    $course->is_free = $request->is_free;
    $course->available_until = ['start_date' => $request->start_date, 'end_date' => $request->end_date];
    $course->training_hours = $request->training_hours;
    $course->training_credits = $request->training_credits;
    $course->type_of_learning = $request->type_of_learning;
    $course->credit_training_hours = $request->credit_training_hours;
    $course->facilitator = $request->facilitator;

    $course->save();

    return $this->sendResponse([], $course->title . ' has been updated.', 200);
  }

  public function forceDelete($course_id)
  {
    $course = Course::withTrashed()->findOrFail($course_id);

    $course->scorm_activities()->forceDelete();
    $course->modules()->forceDelete();
    $course->sub_topics()->forceDelete();
    $course->topics()->forceDelete();
    $course->assessments()->forceDelete();
    $course->announcements()->forceDelete();
    $course->zoom_meetings()->forceDelete();
    $course->enrollments()->forceDelete();
    $course->course_evaluations()->forceDelete();
    $course->module_evaluations()->each(function($module_evaluation) {
      $module_evaluation->student_module_evaluation_answers()->forceDelete();
    });
    $course->module_evaluations()->forceDelete();
    $course->invoices()->forceDelete();

    $course->class_categories()->detach();
    $course->shared_to_users()->detach();
    $course->cart_items()->detach();

    $course->forceDelete();

    return $this->sendResponse([], $course->title . ' has been forced deleted.', 200);
  }


  public function destroy($id)
  {
    $course = Course::find($id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $course->delete();
    if($course->scorm->isNotEmpty()){
      foreach ($course->scorm as $scorm) {
        ScormCloudService::deleteCourse($scorm->scorm_cloud_course_id);
      }
    }
    $course->scorm()->delete();
    $course->class_categories()->detach();
    $course->modules()->delete();
    $course->topics()->delete();
    $course->sub_topics()->delete();
    $course->assessments()->delete();
    $course->announcements()->delete();
    $course->zoom_meetings()->delete();
    $course->shared_to_users()->detach();
    $course->enrollments()->delete();
    $course->course_evaluations()->delete();
    $course->cart_items()->detach();
    $course->invoices()->delete();

    return $this->sendResponse([], $course->title . ' has been deleted.', 200);
  }

  public function deletedCourses(Request $request)
  {
    $courses = Course::onlyTrashed()->where('uploader_id', $this->authUser()->id)->with(['shared_to_users', 'my_permission'])->latest()->paginate($request->paginate);

    return $this->sendResponse($courses, 'Courses in trash.', 200);
  }

  public function restoreCourse(Request $request)
  {
    $request->validate([
      'course_id' => 'required|exists:courses,id'
    ]);

    $course = Course::onlyTrashed()
      ->where('uploader_id', $this->authUser()->id)
      ->where('id', $request->course_id)
      ->first();

    if (!$course) {
      return $this->sendResponse([], 'Course not found.', 404);
    }

    $course->restore();

    return $this->sendResponse($course, 'Course has been restored.', 200);
  }

  public function uploadCourseImage(Request $request, $course_id)
  {
    $request->validate([
      'image_file' => 'required|mimes:jpg,jpeg,png'
    ]);

    $course = Course::find($course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $result = ImageService::uploadCourseImage([
      'model_id' => $course->id,
      'model_type' => 'App\Models\Course',
      'image' => $request->file('image_file')
    ]);

    return $this->sendResponse($result, 'File has been uploaded.', 201);
  }

  public function deleteCourseImage($course_id)
  {
    $course = Course::find($course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $course->image()->delete();

    return $this->sendResponse([], 'File has been deleted.', 201);
  }

  public function setCourseCertificateTemplate(Request $request,  $course_id)
  {
    $request->validate([
      'certificate_template_id' => 'nullable|exists:certificate_templates,id'
    ]);

    $course = Course::find($course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }  

    if (is_null($request->certificate_template_id)) {

      CourseCertificateTemplate::where('course_id', $course->id)->delete();
      
      return $this->sendResponse([], 'Certificate template removed successfully.', 201);
      
    }

    CourseCertificateTemplate::updateOrCreate(
      [ 'course_id' => $course->id ],
      [
        'certificate_template_id' => $request->certificate_template_id,
      ]
    );

    $certificate_template = CourseCertificateTemplate::where('course_id', $course->id)->first();

    return $this->sendResponse($certificate_template, 'Certificate template updated successfully.', 201);

  }

  public function updateCourseStatus(UpdateCourseStatusRequest $request, $id)
  {
    $course = Course::find($id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $course->status = $request->status;
    $is_dirty = $course->isDirty('status');

    $course->save();

    return $this->sendResponse($course, 'Course status has been updated.', 200);
  }

  public function tagClasses($course_id)
  {
    $course = Course::find($course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $course->class_categories()->sync(request('class_category_id'));

    return $this->sendResponse($course->class_categories()->get(), 'Class categories has been synced.', 200);
  }

  public function getAllCourses()
  {
    $course = Course::select(['id', 'uuid', 'title', 'available_until', 'type_of_learning', 'price', 'is_free'])->where('status', '!=', Course::IS_DRAFT )->get();

    return $this->sendResponse($course, 'List of all courses', 200);
  }

  public function getCourses($status, Request $request){

    if($request->paginate) {

      $status = $request->has('status') ? $request->status : $status;

      $course = Course::select(['id', 'status', 'title', 'uuid', 'created_at', 'type_of_learning', 'uploader_id', 'course_code', 'available_until'])
        ->when(!in_array($status, ['ALL', 'dashboard']), function($query) use ($status){
          $query->where('status', $status == 'published' ? Course::IS_PUBLISH : ( $status == 'unpublished' ? Course::IS_UNPUBLISH : Course::IS_DRAFT));
        })
        ->with([
          'shared_to_users',
          'my_permission',
        ])
        ->when($request->search, function ($query) use ($request) {
          FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
        })
        ->when(($request->created_from || $request->created_to), function ($query) use ($request) {
          FilterHelper::queryCreatedAt($query, $request->created_from, $request->created_to);
        })
        ->when(($request->type_of_learning), function ($query) use ($request) {
          $query->where('type_of_learning', $request->type_of_learning);
        })
        ->when($request->ownership, function($query) use ($request) {
          if($request->ownership == Course::IS_OWNED) {
            $query->where('uploader_id', $this->authUser()->id);
          } else if ($request->ownership == Course::IS_SHARED) { 
            $query->whereHas('shared_to_users', function ($subquery) {
                $subquery->where('user_id', $this->authUser()->id);
              });
          }
        })
        ->when(!$request->ownership, function($query) {
          $query->where(function ($query) {
            $query->where('uploader_id', $this->authUser()->id)
              ->orWhereRelation('shared_to_users', 'user_id', $this->authUser()->id);
          });
        })
        ->latest()
        ->paginate($request->paginate);
  
      $course->makeHidden([
        'user_is_enrolled',
        'course_card',
        'course_is_open_for_registration']);
    } else {
      
    $status = $request->has('status') ? $request->status : $status;

    $course = Course::select(['id', 'status', 'title', 'uuid', 'created_at', 'type_of_learning', 'uploader_id', 'course_code', 'available_until'])
      ->when(!in_array($status, ['ALL', 'dashboard']), function($query) use ($status){
        $query->where('status', $status == 'published' ? Course::IS_PUBLISH : ( $status == 'unpublished' ? Course::IS_UNPUBLISH : Course::IS_DRAFT));
      })
      ->with([
        'shared_to_users',
        'my_permission',
      ])
      ->when($request->search, function ($query) use ($request) {
        FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
      })
      ->when(($request->created_from || $request->created_to), function ($query) use ($request) {
        FilterHelper::queryCreatedAt($query, $request->created_from, $request->created_to);
      })
      ->when($request->ownership, function($query) use ($request) {
        if($request->ownership == Course::IS_OWNED) {
          $query->where('uploader_id', $this->authUser()->id);
        } else if ($request->ownership == Course::IS_SHARED) { 
          $query->whereHas('shared_to_users', function ($subquery) {
              $subquery->where('user_id', $this->authUser()->id);
            });
        }
      })
      ->when(!$request->ownership, function($query) {
        $query->where(function ($query) {
          $query->where('uploader_id', $this->authUser()->id)
            ->orWhereRelation('shared_to_users', 'user_id', $this->authUser()->id);
        });
      })
      ->latest()
      ->get();
    }

    return $this->sendResponse($course, 'List of '. $request->ownership . ' '. $status .' courses', 200);
  }

  public function getModules($course_id)
  {
    $course = Course::with(['modules' => function ($query) {
      $query->with([
        'module_content' => function ($query) {
          $query->with([
              'contentable' => function ($morphTo) {
                  $morphTo->morphWith([
                      \App\Models\Topic::class => [
                          'scorm',
                          'topic_has_assessments',
                          'learning_material',
                          'sub_topics' => function ($query) {
                              $query->with([
                                  'scorm',
                                  'sub_topic_has_assessments',
                                  'learning_material'
                              ])->orderBy('sort_no', 'ASC');
                          }
                      ],
                      \App\Models\Assessment::class => []
                  ]);
              }
          ]);
        },
        'module_evaluations'
      ]);
    }])->find($course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    return $this->sendResponse($course->modules, 'List of all modules under ' . $course->title, 200);
  }

  public function getAssessments($course_id)
  {
    $course = Course::with(['assessments.assessment_questions.assessment_answer'])->find($course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    return $this->sendResponse($course->assessments, 'List of all assessments under ' . $course->title, 200);
  }

  public function getCourseEvaluations($course_id) 
  {
    $course_evaluations = CourseEvaluation::where('course_id', $course_id)->get();

    return $this->sendResponse($course_evaluations, 'List of all course evaluations under course' , 200);

  }

  public function shareCourse(Request $request)
  {
    $request->validate([
      'course_id' => 'required|exists:courses,id',
      'user_id.*' => 'exists:users,id'
    ]);

    $course = Course::find($request->course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    foreach ($request->user_id as $user_id) {
      $user = User::where('id', $user_id)->first();
      $apiKey = config("services.sender.key");

      if($apiKey) {
        SendCourseInvitationViewerEmail::dispatch([
          'name' => $user->full_name,
          'course_title' => $course->title,
          'course_description' => $course->description,
          'course_status' => $course->status,
          'course_image' => $course->image->url ?? null,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId(),
          'email' => $user->email
        ]);
      } else {
        Mail::to($user->email)->send(new CourseInvitationViewer([
          'name' => $user->full_name,
          'course_title' => $course->title,
          'course_description' => $course->description,
          'course_status' => $course->status,
          'course_image' => $course->image->url ?? null,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]));
      }
    }

    $course->shared_to_users()->syncWithoutDetaching($request->user_id, 'editor');

    return $this->sendResponse([], 'Course has been shared.', 201);
  }

  public function revokeCourseAccess(Request $request)
  {
    $request->validate([
      'user_id' => 'required|exists:users,id',
      'course_id' => 'required|exists:courses,id'
    ]);

    $course = Course::find($request->course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $course->shared_to_users()->detach($request->user_id);

    return $this->sendResponse([], 'Instructor has been unassigned', 200);
  }

  public function shareCourseUsers(Request $request)
  {
    $users = User::whereIn('role', [User::ROLE_INSTRUCTOR, User::ROLE_ADMIN])
      ->where('id', '!=', $this->authUser()->id)
      ->whereDoesntHave('shared_courses', function (Builder $query) use ($request) {
        $query->where('course_id', $request->course_id);
      })
      ->get();

    return $this->sendResponse($users, 'List of all users.', 200);
  }

  public function shareCoursePermission(Request $request)
  {
    $request->validate([
      'course_id' => 'required|exists:courses,id',
      'user_id' => 'exists:users,id',
      'permission' => 'required|in:viewer,editor'
    ]);

    $course = Course::find($request->course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $user = User::where('id', $request->user_id)->first();

    if ($request->permission === 'viewer') {

      $apiKey = config("services.sender.key");

      if($apiKey) { 
        SendCourseInvitationViewerEmail::dispatch([
          'name' => $user->full_name,
          'course_title' => $course->title,
          'course_description' => $course->description,
          'course_status' => $course->status,
          'course_image' => $course->image->url ?? null,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId(),
          'email' => $user->email
        ]);
      } else {
        Mail::to($user->email)->send(new CourseInvitationViewer([
          'name' => $user->full_name,
          'course_title' => $course->title,
          'course_description' => $course->description,
          'course_status' => $course->status,
          'course_image' => $course->image->url ?? null,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]));
      }

    } else if ($request->permission === 'editor') {

      $apiKey = config("services.sender.key");

      if($apiKey) { 
        SendCourseInvitationEditorEmail::dispatch([
          'name' => $user->full_name,
          'course_title' => $course->title,
          'course_description' => $course->description,
          'course_status' => $course->status,
          'course_image' => $course->image->url ?? null,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId(),
          'email' => $user->email
        ]);
      } else {
        Mail::to($user->email)->send(new CourseInvitationEditor([     
          'name' => $user->full_name,
          'course_title' => $course->title,
          'course_description' => $course->description,
          'course_status' => $course->status,
          'course_image' => $course->image->url ?? null,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]));
      }
    }

    $course->shared_to_users()->where('user_id', $request->user_id)
      ->update(['permission' => $request->permission]);

    return $this->sendResponse([], 'Permission has been set.', 201);
  }

  public function getCourseCertificatesEnrollees($course_id)
  {
    
    $searchParts = explode(' ', request('search'));
    
    $enrollees = Enrollment::where('course_id', $course_id)
      ->has('user')
      ->when(request('search'), function($query) use ($searchParts) {
          $query->whereHas('user', function ($subQuery)  use ($searchParts)  {
              Helper::queryNameEmailSearch($searchParts, $subQuery);
          });
      })
      ->with(['user' => function($query) use ($course_id) {
        $query->select(['id', 'first_name', 'last_name', 'suffix', 'email'])
        ->with(['certificate_template_categories' => function($query) use ($course_id) {
          $query->wherePivot('course_id', $course_id);
        }]);
      }])
      ->paginate(request('paginate'));

      return $this->sendResponse($enrollees, 'Course Participants', 200);
      
  }

  public function setAssignCertificateEnrollees(Request $request, $course_id)
  {
    $request->validate([
      'user_id' => "required|exists:users,id",
      'certificate_template_categories_id' => 'required|array|min:1',
      'certificate_template_categories_id.*' => 'required|exists:certificate_template_categories,id',
    ]);

    $user = User::findOrFail($request->user_id);

    $syncData = [];
    foreach ($request->certificate_template_categories_id as $category_id) {
        $syncData[$category_id] = ['course_id' => $course_id];
    }
    
    $user->certificate_template_categories()->sync($syncData);

    return $this->sendResponse([], 'Certificates assigned successfully', 200);
    
  }

  public function getTrainingCredits()
  {
    
    $training_credits = TrainingCredit::
        when(request('search'), function ($query) {
          $query->where('name', 'LIKE', '%' . request('search') . '%');
        })->get();

    return $this->sendResponse($training_credits, 'List of all training credits.', 200);
    
  }

  public function downloadContent(Request $request)
  {
    $request->validate([
        'url' => 'required|url',
    ]);

    $presignedUrl = S3Helper::generatePresignedDownloadUrl($request->url);

    return response()->json([
        'result' => $presignedUrl ? 'success' : 'failed',
        'downloadable_url' => $presignedUrl
    ]);
  }
  
  public function createGroupChat($course_id, Request $request)
  {
    $course = Course::select('id')->findOrFail($course_id);

    $request->validate([
      'user_id.*' => 'required|exists:users,id',
    ]);

    $group_chat = $course->course_has_group_chat()->create([
      'name' => $course->title . ' enrollees',
      'user_id' => $this->authUser()->id
    ]);

    $users = User::select(['id', 'first_name', 'last_name', 'suffix', 'email'])->whereIn('id', $request->user_id)->get();
    
    foreach ($users as $user) {
        $group_chat->group_messages()->create([
            'group_chat_id' => $group_chat->id,
            'message_body' => $user->full_name . ' has been added to the group.',
            'synced_users' => true,
            'sender_id' => $this->authUser()->id
        ]);

        $data = [
            'name' => $this->authUser()->full_name,
            'group_name' => $group_chat->name,
            'group_chat_uuid' => $group_chat->uuid,
            'synced_users' => true,
            'action' => 'added'
        ];

        Notification::send($user, new GroupMessageNotification($data));
    }

    $group_chat->group_chat_users()->syncWithoutDetaching(array_push($request->user_id,  $this->authUser()->id));

    $group_chat->touch();

    return $this->sendResponse($group_chat, 'You have successfully created a group chat.', 201);

  }

  public function getCourseGroupChats($course_id)
  {

    $course = Course::select('id')->findOrFail($course_id);

    $course_group_chats = $course->getPaginatedGroupChats(request('paginate'));

    return $this->sendResponse($course_group_chats, 'You have successfully created a course group chat', 200);
    
  }

  public function deleteCourseGroupChat($course_id, $group_chat_id)
  {
    $course = Course::select('id')->findOrFail($course_id);

    $course->course_has_group_chat()->detach($group_chat_id);

    $group_chat = GroupChat::find($group_chat_id);

    $group_chat->delete();

    return $this->sendResponse([], 'You have successfully deleted a course group chat', 200);

  }

  public function duplicateCourse($course_id)
  {

    DB::beginTransaction();

    try {
        $original_course = Course::with([
          'image',
          'course_card',
          'modules' => function ($query) use ($course_id){
            $query->with([
              'group_activities' => function($subquey) use ($course_id){
                $subquey->where('course_id', $course_id);
              },
              'topics' => function ($query) {
                $query->with([
                  'learning_material',
                  'topic_has_assessments' => function ($query) {
                    $query->with(['assessment_questions']);
                  },
                  'sub_topics' => function ($query) {
                    $query->with([
                      'learning_material',
                      'sub_topic_has_assessments' => function ($query) {
                        $query->with(['assessment_questions']);
                      },
                    ])->orderBy('sort_no', 'ASC');
                  }
                ])->orderBy('sort_no', 'ASC');
              },
              'assessments' =>  function ($query) {
                $query->whereHas('module_content')->with(['assessment_questions']);
              },
              'module_evaluations' => function ($query) {
                $query->with(['module_evaluation_questions' => function ($subquery) {
                  $subquery->with(['image']);
                }]);
              }
            ]);
          },
          'class_categories',
          'course_evaluations' => function ($query) {
            $query->with(['course_evaluation_questions' => function ($subquery) {
              $subquery->with(['image']);
            }]);
          }
        ])->findOrFail($course_id);

        $course_count = Course::where('title', 'LIKE', '%' . $original_course->title . '%')->count();

        $new_course = $original_course->replicate()->fill([
            'title' => $original_course->title . '.copy' . ($course_count + 1)
        ]);
        $new_course->uploader_id = $this->authUser()->id;
        $new_course->save();

        // Duplicate image
        if ($original_course->image) {
            $new_image = $original_course->image->replicate();
            $new_image->imageable_id = $new_course->id;
            $new_image->imageable_type = Course::class;
            $new_image->save();
        }

        // Sync class_categories (pivot table)
        $new_course->class_categories()->sync($original_course->class_categories->pluck('id'));

        // Duplicate modules and nested topics + subtopics
        foreach ($original_course->modules as $module) {
          $new_module = $module->replicate();
          $new_module->course_id = $new_course->id;
          $new_module->uploader_id = $this->authUser()->id;
          $new_module->save();

          foreach ($module->topics as $topic) {
            $new_topic = $topic->replicate();
            $new_topic->course_id = $new_module->course_id;
            $new_topic->module_id = $new_module->id;
            $new_topic->uploader_id = $this->authUser()->id;
            $new_topic->save();
            
            $new_module->module_content()->create([
              'module_id' => $new_module->id,
              'contentable_id' => $new_topic->id,
              'contentable_type' => 'App\\Models\\Topic',
              'sort_no' => $new_module->module_content_count + 1
            ]);

            if ($topic && $topic->learning_material) {
                $learning_material = $topic->learning_material->replicate();
                $learning_material->learnable_id = $new_topic->id;
                $learning_material->learnable_type = Topic::class;
                $learning_material->save();
            }

            foreach ($topic->topic_has_assessments as $assessment) {
              $new_assessment = $assessment->replicate();
              $new_assessment->course_id = $new_module->course_id;
              $new_assessment->module_id = $new_module->id;
              $new_assessment->uploader_id = $this->authUser()->id;
              $new_assessment->save();

              $new_assessment->assessment_has_topics()->attach($new_topic->id);
    
              foreach ($assessment->assessment_questions as $key => $assessment_question) {
                $new_question = $new_assessment->assessment_questions()->create($assessment_question->toArray());
                $assessment_answer = AssessmentAnswer::where('assessment_question_id', $assessment_question->id)->first();
                $assessment_answer->replicate(['assessment_question_id'])
                  ->fill([
                    'assessment_question_id' => $new_question->id,
                    'correct_answer' => $assessment_answer->correct_answer,
                    'choices' => $assessment_answer->choices
                  ])
                  ->save();
              }
            }

            foreach ($topic->sub_topics as $sub_topic) {
              $new_subtopic = $sub_topic->replicate();
              $new_subtopic->course_id = $new_module->course_id;
              $new_subtopic->topic_id = $new_topic->id;
              $new_subtopic->uploader_id = $this->authUser()->id;
              $new_subtopic->save();

              if ($sub_topic && $sub_topic->learning_material) {
                $learning_material = $new_subtopic->learning_material->replicate();
                $learning_material->learnable_id = $new_subtopic->id;
                $learning_material->learnable_type = SubTopic::class;
                $learning_material->save();
              }
              
              foreach ($sub_topic->sub_topic_has_assessments as $assessment) {
                $new_assessment = $assessment->replicate();
                $new_assessment->course_id = $new_module->course_id;
                $new_assessment->module_id = $new_module->id;
                $new_assessment->uploader_id = $this->authUser()->id;
                $new_assessment->save();

                $new_assessment->assessment_has_sub_topics()->attach($new_subtopic->id);
      
                foreach ($assessment->assessment_questions as $key => $assessment_question) {
                  $new_question = $new_assessment->assessment_questions()->create($assessment_question->toArray());
                  $assessment_answer = AssessmentAnswer::where('assessment_question_id', $assessment_question->id)->first();
                  $assessment_answer->replicate(['assessment_question_id'])
                    ->fill([
                      'assessment_question_id' => $new_question->id,
                      'correct_answer' => $assessment_answer->correct_answer,
                      'choices' => $assessment_answer->choices
                    ])
                    ->save();
                }
              }
            }
          }

          // Duplicate group activities
          foreach ($module->group_activities as $activity) {
            $new_activity = $activity->replicate();
            $new_activity->course_id = $new_module->course_id;
            $new_activity->module_id = $new_module->id;
            $new_activity->uploader_id = $this->authUser()->id;
            $new_activity->group_count = 0;
            $new_activity->save();
            
            $module_content = ModuleContent::where('contentable_id', $activity->id)->where('contentable_type', 'App\\Models\\GroupActivity')->first();

            $new_module->module_content()->create([
              'module_id' => $new_module->id,
              'contentable_id' => $new_activity->id,
              'contentable_type' => 'App\\Models\\GroupActivity',
              'sort_no' => $module_content->sort_no
            ]);
          }
        
          foreach ($module->assessments as $assessment) {
            $new_assessment = $assessment->replicate();
            $new_assessment->course_id = $new_module->course_id;
            $new_assessment->module_id = $new_module->id;
            $new_assessment->uploader_id = $this->authUser()->id;
            $new_assessment->save();
  
            foreach ($assessment->assessment_questions as $key => $assessment_question) {
              $new_question = $new_assessment->assessment_questions()->create($assessment_question->toArray());
              $assessment_answer = AssessmentAnswer::where('assessment_question_id', $assessment_question->id)->first();
              $assessment_answer->replicate(['assessment_question_id'])
                ->fill([
                  'assessment_question_id' => $new_question->id,
                  'correct_answer' => $assessment_answer->correct_answer,
                  'choices' => $assessment_answer->choices
                ])
                ->save();
            }

            $module_content = ModuleContent::where('contentable_id', $assessment->id)->where('contentable_type', 'App\\Models\\Assessment')->first();

            $new_module->module_content()->create([
              'module_id' => $new_module->id,
              'contentable_id' => $new_assessment->id,
              'contentable_type' => 'App\\Models\\Assessment',
              'sort_no' => $module_content->sort_no
            ]);
          }

          // Duplicate module evaluations
          foreach ($module->module_evaluations as $module_evaluation) {
            $new_module_evaluation = $module_evaluation->replicate();
            $new_module_evaluation->module_id = $new_module->id;
            $new_module_evaluation->uploader_id = $this->authUser()->id;
            $new_module_evaluation->save();

              // Duplicate related questions
            foreach ($new_module_evaluation->module_evaluation_questions as $question) {
              $new_question = $question->replicate();
              $new_question->module_evaluation_id = $new_module_evaluation->id;
              $new_question->save();

              foreach ($question->image as $image) {
                $new_image = $image->replicate();
                $new_image->imageable_id = $new_question->id;
                $new_image->save();
              }
            }
          }

        }

        // Duplicate course evaluations
        foreach ($original_course->course_evaluations as $course_evaluation) {
          $new_course_evaluation = $course_evaluation->replicate();
          $new_course_evaluation->course_id = $new_course->id;
          $new_course_evaluation->uploader_id = $this->authUser()->id;
          $new_course_evaluation->save();

            // Duplicate related questions
          foreach ($new_course_evaluation->course_evaluation_questions as $question) {
            $new_question = $question->replicate();
            $new_question->course_evaluation_id = $new_course_evaluation->id;
            $new_question->save();

            foreach ($question->image as $image) {
              $new_image = $image->replicate();
              $new_image->imageable_id = $new_question->id;
              $new_image->save();
            }
          }
        }

        DB::commit();

        return $this->sendResponse($new_course->id, 'Course duplicated with content.', 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return $this->sendResponse([], 'Failed to duplicate course: ' . $e->getMessage(), 500);
    }

  }

}
