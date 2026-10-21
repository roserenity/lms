<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;

use App\Models\Course;
use App\Models\CourseCardColor;
use App\Models\ClassCategory;
use App\Models\Enrollment;
use App\Models\AssessmentScore;
use App\Models\StudentCompleteCourse;
use App\Models\ScormRegistrationInteraction;
use App\Models\ZoomMeeting;
use App\Models\User;
use App\Models\Scorm;
use App\Models\Module;
use App\Models\CustomModuleField;
use App\Models\Invoice;
use App\Models\GroupActivity;

use App\Services\Tenants\LearningMaterialService;
use App\Services\Tenants\ScormCloudService;

use Spatie\Activitylog\Models\Activity;

use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Models\ScormRegistration;
use App\Models\ScormRegistrationProgress;
use App\Models\StudentModuleProgress;
use Carbon\Carbon;


use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;

use Str;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function myCourses(Request $request)
    {
        $enrolled_courses = Enrollment::enrolled()
            ->whereHas(
                'course', function($query) use ($request) {
                    $query->when(!empty(request('classes')), function ($query) use ($request) {
                        $query->whereHas('class_categories', function ($subquery) use ($request) {
                            $subquery->where('class_code', $request->classes);
                        });
                    })
                    ->when(!empty($request->type_of_learning), function ($query) use ($request) {
                        $query->where('type_of_learning', $request->type_of_learning);
                    })
                    ->when($request->search, function ($query) use ($request) {
                        FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
                    });
                }
            )
            ->with([
                'course' =>  function($query) use ($request) {
                    $query->withCount(['training_credit', 'class_categories', 'assessments', 'topics', 'sub_topics', 'modules', 'course_evaluations', 'module_evaluations', 'zoom_meetings', 'group_activities']);
                }
            ])
            ->when($request->course_name_sort, function ($query) use ($request) {
                $query->join('courses', 'enrollments.course_id', '=', 'courses.id')
                    ->orderBy('courses.title', $request->course_name_sort)
                    ->select('enrollments.*');
            })
            ->when($request->availability_date_sort, function ($query) use ($request) {
                $direction = strtoupper($request->availability_date_sort) === 'ASC' ? 'ASC' : 'DESC';

                $nullOrder = $direction === 'ASC' ? 1 : 0;
                $notNullOrder = $direction === 'ASC' ? 0 : 1;

                $query->join('courses', 'enrollments.course_id', '=', 'courses.id')
                    ->orderByRaw("
                        CASE
                            WHEN
                                COALESCE(
                                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(courses.available_until, '$.start_date')), ''),
                                    NULLIF(JSON_UNQUOTE(JSON_EXTRACT(courses.available_until, '$.end_date')), '')
                                ) IS NULL
                            THEN $nullOrder
                            ELSE $notNullOrder
                        END,
                        COALESCE(
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(courses.available_until, '$.start_date')), ''),
                            NULLIF(JSON_UNQUOTE(JSON_EXTRACT(courses.available_until, '$.end_date')), '')
                        ) $direction
                    ")
                    ->select('enrollments.*');


            })
            ->paginate($request->paginate);
        
        foreach ($enrolled_courses as $enrolled_course) {
            $enrolled_course->course = Helper::setLearnerCourseData($enrolled_course->course, $this->authUser()->id);
            $enrolled_course->course['total_checked_assessments'] = $enrolled_course->course->total_checked_assessments ?? 0;
        }

        /* Log::info(json_encode($enrolled_course));

        $enrolled_course_ids = Enrollment::enrolled()->get()->pluck('course_id');

        $courses = Course::whereIn('id', $enrolled_course_ids)
            ->withCount(['assessments', 'topics', 'sub_topics', 'modules', 'course_evaluations', 'module_evaluations', 'zoom_meetings'])
            ->when($request->course_name_sort, function ($query) use ($request) {
                $query->orderBy('title', $request->course_name_sort);
            })
            ->when(!empty(request('classes')), function ($query) use ($request) {
                $query->whereHas('class_categories', function ($subquery) use ($request) {
                    $subquery->where('class_code', $request->classes);
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->search. '%');
            })
            ->paginate($request->paginate);
        
        foreach ($courses as $course) {
            $course = Helper::setLearnerCourseData($course, $this->authUser()->id);
            $course['total_checked_assessments'] = $course->total_checked_assessments ?? 0;
        }
 */
        return $this->sendResponse($enrolled_courses, 'List of all enrolled courses.', 200);

        //$favorite_courses = $this->authUser()->user_has_course_favorites;

        //return $this->sendResponse($favorite_courses->merge($courses->diff($favorite_courses)), 'List of all courses.', 200);
    }

    public function setAssessmentScore($assessment)
    {
        $assessment_scores = AssessmentScore::where('assessment_id', $assessment->id)
            ->where('user_id', $this->authUser()->id);
        
        $score = $assessment_scores->where('active', 1)->first();

        $assessment['assessment_score'] = [
            'score' =>  $score->score ?? null,
            'attempts' => $assessment_scores->count(),
            'created_at' => $score->created_at ?? null,
            'checked' => $score->checked ?? null,
        ];
    }

    public function getCourseEvaluations($course_evaluations)
    {
        return $course_evaluations->withCount([
                'student_course_evaluations' => function ($student_course_evaluations) {
                    return $student_course_evaluations->where('user_id', $this->authUser()->id);
                }, 
                'course_evaluation_questions'
            ])
             ->with([
                 'student_course_evaluations' => function ($student_course_evaluations) {
                     return $student_course_evaluations->where('user_id', $this->authUser()->id)->orderBy('id', 'DESC');
                 },
                'student_course_evaluation_progress'
            ]);
    }

    public function getModuleEvaluations($module_evaluations)
    {
        return $module_evaluations->withCount([
            'student_module_evaluations' => function ($student_module_evaluations) {
                return $student_module_evaluations->where('user_id', $this->authUser()->id);
            }, 
            'module_evaluation_questions'
        ])
        ->with([
            'student_module_evaluations' => function ($student_module_evaluations) {
                return $student_module_evaluations->where('user_id', $this->authUser()->id)->orderBy('id', 'DESC');
            },
            'student_module_evaluation_progress'
        ]); 
    }

    public function getTopics($topics, $learning_material)
    {
        $topics->with([
            'topic_has_assessments' => function ($query) {
                $query->with('student_assessment_progress');
            },
            'scorm' => function ($scorm) {
                $scorm->with('my_registration');
            },
            'scorm_activities' => function ($scorm_activities) {
                $scorm_activities->with('my_scorm_answers', function($query) {
                    $query->with('scorm_question');
                });
            },
            'student_scorm_progress',
            'student_topic_progress',
            'sub_topics' => function ($sub_topics) use ($learning_material){
                $sub_topics->with([
                    'scorm' => function ($scorm) {
                        $scorm->with('my_registration');
                    },
                    'sub_topic_has_assessments' => function ($query) {
                        $query->with('student_assessment_progress');
                    },
                    'scorm_activities' => function ($scorm_activities) {
                        $scorm_activities->with('my_scorm_answers', function($query) {
                            $query->with('scorm_question');
                        });
                    },
                    'student_scorm_progress',
                    'student_sub_topic_progress'
                ]);
            
                if ($learning_material) {
                    $sub_topics->with([
                        'learning_material' => function ($query) {
                            $query->select(['id', 'file_type', 'learnable_id', 'url', 'is_downloadable']);
                            $query->getModel()->appendVimeoAttributes();
                        }
                    ]);
                } else {
                    $sub_topics->with([
                        'learning_material' => function ($learning_material) {
                            $learning_material->select(['id', 'file_type', 'learnable_id', 'is_downloadable']);
                        },
                    ]);
                }

                $sub_topics->orderBy('sort_no', 'ASC')
                    ->select([
                        'id',
                        'uuid',
                        'course_id',
                        'topic_id',
                        'title'
                    ]);

            }])
            ->orderBy('sort_no', 'ASC')
            ->select([
                'id',
                'uuid',
                'course_id',
                'module_id',
                'title',
            ]);

        if ($learning_material) {
            $topics->with([
                'learning_material' => function ($query) {
                    $query->select(['id', 'file_type', 'learnable_id', 'url', 'is_downloadable']);
                    $query->getModel()->appendVimeoAttributes();
                }
            ]);
        }   else {
            $topics->with([
                'learning_material' => function ($learning_material) {
                    $learning_material->select(['id', 'file_type', 'learnable_id', 'is_downloadable']);
                }
            ]);
        }

        return $topics;
    }
    
    public function getAssessmentScore($module_content)
    {
            if ($module_content->contentable_type === \App\Models\Topic::class) {
                if($module_content->contentable) {
                    if($module_content->contentable->topic_has_assessments) {
                        foreach ($module_content->contentable->topic_has_assessments as $assessment) {
                            $this->setAssessmentScore($assessment);
                        }
                    }
        
                    if ($module_content->contentable['sub_topics']) {
                        if($module_content->contentable->sub_topics) {
                            foreach ($module_content->contentable->sub_topics as $sub_topic) {
                                foreach ($sub_topic->sub_topic_has_assessments as $assessment) {
                                    $this->setAssessmentScore($assessment);
                                }
                            }
                        }
                    }
                }
            }
            if ($module_content->contentable_type === \App\Models\Assessment::class) {
                if($module_content->contentable) {
                    $this->setAssessmentScore($module_content->contentable);
                }
            }

    }
    

    public function showCourse($course_uuid, Request $request)
    {

        $selected = Course::where('uuid', $course_uuid)->first();

        if(!$selected) {
            return $this->sendResponse([], 'Course is unavailable', 404);
        }

        $enrolled = Enrollment::where('user_id', $this->authUser()->id)->where('course_id', $selected->id)->exists();

        if(!$enrolled) {
            return $this->sendResponse([], 'Enrollment revoked', 404);
        }
        
        if ($request->learning_material) {

            $scorms = Scorm::where('course_id', $selected->id)->with('my_registration')->get();
    
            foreach($scorms as $scorm) {
    
                $registration_id = $scorm->scorm_cloud_course_id .'-' . $this->authUser()->id;
    
                $result1 = ScormCloudService::getRegistration($this->authUser()->id, $scorm->scorm_cloud_course_id);
    
                Log::info('getRegistration'.json_encode($result1));
    
                if($result1['result'] === 'success') {

                    if (!$scorm->launch_link) {
                        $createLaunchResult = ScormCloudService::createLaunchLink($this->authUser()->id, $scorm->id, $scorm->scorm_cloud_course_id, $registration_id);
                        if ($createLaunchResult == 'error') {
                            return $this->sendResponse([], $createLaunchResult, 502);
                        }
                    }
                    
                } else {
                    // Registration does not exist, create a new one
                    $createRegistrationResult = ScormCloudService::createRegistration($this->authUser()->id, $scorm->scorm_cloud_course_id);
                    if ($createRegistrationResult['result'] == 'error') {
                        return $this->sendResponse([], 'Scorm Registration error', 502);
                    }
        
                    // Create launch link after registration
                    $createLaunchResult = ScormCloudService::createLaunchLink($this->authUser()->id, $scorm->id, $scorm->scorm_cloud_course_id, $registration_id);
                    if ($createLaunchResult['result'] == 'error') {
                        return $this->sendResponse([], $createLaunchResult, 502);
                    }
                }
            }

        }

        $course = Course::with([
            'modules' => function ($modules) use ($request) {
                return $modules->with([
                        'module_evaluations' => function ($module_evaluations) {
                            return $this->getModuleEvaluations($module_evaluations);
                        },
                        'module_content' => function ($query) use ($request) {
                            $query->with([
                                'contentable' => function ($morphTo) use ($request) {
                                    $morphTo->morphWith([
                                        \App\Models\Topic::class => [
                                                'topic_has_assessments' => function ($query) {
                                                    $query->with('student_assessment_progress');
                                                },
                                                'scorm' => function ($scorm) {
                                                    $scorm->with('my_registration');
                                                },
                                                'scorm_activities' => function ($scorm_activities) {
                                                    $scorm_activities->with('my_scorm_answers', function($query) {
                                                        $query->with('scorm_question');
                                                    });
                                                },
                                                'student_scorm_progress',
                                                'student_topic_progress',
                                                'sub_topics' => function ($sub_topics) use ($request){
                                                    $sub_topics->with([
                                                        'scorm' => function ($scorm) {
                                                            $scorm->with('my_registration');
                                                        },
                                                        'sub_topic_has_assessments' => function ($query) {
                                                            $query->with('student_assessment_progress');
                                                        },
                                                        'scorm_activities' => function ($scorm_activities) {
                                                            $scorm_activities->with('my_scorm_answers', function($query) {
                                                                $query->with('scorm_question');
                                                            });
                                                        },
                                                        'student_scorm_progress',
                                                        'student_sub_topic_progress'
                                                    ]);
                                                
                                                    if ($request->learning_material) {
                                                        $sub_topics->with([
                                                            'learning_material' => function ($query) {
                                                                $query->select(['id', 'file_type', 'learnable_id', 'url', 'is_downloadable']);
                                                                $query->getModel()->appendVimeoAttributes();
                                                            }
                                                        ]);
                                                    } else {
                                                        $sub_topics->with([
                                                            'learning_material' => function ($learning_material) {
                                                                $learning_material->select(['id', 'file_type', 'learnable_id', 'is_downloadable']);
                                                            },
                                                        ]);
                                                    }
                                    
                                                    $sub_topics->orderBy('sort_no', 'ASC')
                                                        ->select([
                                                            'id',
                                                            'uuid',
                                                            'course_id',
                                                            'topic_id',
                                                            'title'
                                                        ]);
                                    
                                                },
                                                'learning_material' => function ($learning_material) use ($request) {
                                                    $this->applyLearningMaterialQuery($learning_material, $request);
                                                }
                          
                                        ],
                                        \App\Models\Assessment::class => ['student_assessment_progress'],
                                        \App\Models\GroupActivity::class => [
                                            'group_activity_groups' => function ($query) {
                                                $query->whereHas('group_activity_has_group_members', function ($subquery) {
                                                    $subquery->where('user_id', $this->authUser()->id);
                                                })->first();
                                            }
                                        ]
                                    ]);
                                }
                            ]);
                        },
                    ])->selectAttributes();
                },
            'uploader' => fn ($uploader) => $uploader->select(['id', 'first_name', 'last_name', 'suffix']),
            'course_evaluations' => function ($course_evaluations) {
                return $this->getCourseEvaluations($course_evaluations);
            }
        ])->select([
            'id',
            'type_of_learning',
            'title',
            'training_hours',
            'facilitator',
            'description',
            'uploader_id',
            'uuid',
            'available_until',
            'training_credits'
        ])
        ->where('uuid', $course_uuid)
        ->first()
        ->makeHidden([
            'course_card',
            'assessments'
        ]);

        $course->is_completed = StudentCompleteCourse::where('user_id', $this->authUser()->id)->where('course_id', $course->id)->exists();

        if (!empty($course->modules)) {
            foreach ($course->modules as $module) {
                foreach ($module->module_content as $module_content) {
                    $this->getAssessmentScore($module_content);
                }
            }
        }

        $invoice = Invoice::where('user_id', $this->authUser()->id)
            ->where('course_id', $course->id)
            ->where(function($query) {
                $query->where('status', Invoice::PAYMENT_SUCCESS)
                    ->orWhere('status', Invoice::PAYMENT_FREE);
            })
            ->exists();

        $checkCourseConducted2024 = $this->checkCourseConducted2024($course);
        
        $course->is_certificate_open = $checkCourseConducted2024 ? true : $invoice;

        $custom_module_fields = CustomModuleField::all();

        $course->custom_module_fields = $custom_module_fields;

        $course->learner_time_spent = $course->getTotalTimeSpent($course->id, $this->authUser()->id);
    
        return $this->sendResponse($course, 'Show user course.', 200);
    }

    protected function applyLearningMaterialQuery($query, $request)
    {
        if ($request->learning_material) {
            $query->select(['id', 'file_type', 'learnable_id', 'url', 'is_downloadable'])->getModel()->appendVimeoAttributes();
        } else {
            $query->select(['id', 'file_type', 'learnable_id', 'is_downloadable']);
        }
    }

    public function checkCourseConducted2024($course)
    {
        try {

            if (!isset($course->available_until)) {
                Log::warning('available_until is not set!');
                return false;
            }

            if (!isset($course->available_until['start_date']) && !isset($course->available_until['end_date'])) {
                Log::warning('Both start_date and end_date are missing!');
                return false;
            }

            Log::info('available_until structure:', $course->available_until);

            $startDate = isset($course->available_until['start_date']) 
                ? Carbon::createFromFormat('Y-m-d', $course->available_until['start_date'], 'UTC')->startOfDay() 
                : null;

            $endDate = isset($course->available_until['end_date']) 
                ? Carbon::createFromFormat('Y-m-d', $course->available_until['end_date'], 'UTC')->startOfDay() 
                : null;

            Log::info('Parsed Dates:', ['startDate' => $startDate, 'endDate' => $endDate]);

            $startOf2024 = Carbon::create('2024-01-01', 'UTC')->startOfDay();
            $endOf2024 = Carbon::create('2024-12-31', 'UTC')->endOfDay();

            $result = ($startDate && $startDate->lte($endOf2024) && (!$endDate || $endDate->gte($startOf2024)));

            Log::info('Final result:', ['result' => $result]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error checking course dates: ' . $e->getMessage());
            return false;
        }
    }

    public function getCourseAnnouncements($id) {
        $announcements = Announcement::with([
            'comments' => fn ($comments) => $comments->with('replies')->orderBy('created_at', 'DESC')
        ])->withCount('comments')->where('course_id', $id)->get();

        return $this->sendResponse($announcements, 'Show course announcements.', 200);
    }

    public function updateCourseCardColor(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'color' => 'required'
        ]);

        CourseCardColor::updateOrCreate(
            ['course_id' => $request->course_id, 'user_id' => $this->authUser()->id],
            ['color' => $request->color]
        );

        return $this->sendResponse([], 'You have successfully set the color.', 201);
    }

    public function updateFavoriteCourse(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'is_favorite' => 'required|boolean'
        ]);

        $course = Course::find($request->course_id);

        if ($request->is_favorite === true) {
            $course->course_favorite_has_users()->attach($this->authUser()->id);
            return $this->sendResponse([], 'You set this course to favorite.', 201);
        } else {
            $course->course_favorite_has_users()->detach($this->authUser()->id);
            return $this->sendResponse([], 'You remove this course to favorite.', 201);
        }
    }

    public function downloadCourseLearningMaterial(Request $request)
    {
        $request->validate([
            'url' => 'required',
            'file_type' => 'required'
        ]);

        if($request->file_type == 'mp4') 
        {
            $response = LearningMaterialService::downloadVideo($request->url);

            return response()->json($response);
        } else {
            $presignedUrl = S3Helper::generatePresignedDownloadUrl($request->url);

            return response()->json([
                'result' => $presignedUrl ? 'success' : 'failed',
                'downloadable_url' => $presignedUrl
            ]);
        }
    }

    public function completedCourses()
    {
        // $completedCourses = StudentCompleteCourse::where('user_id', $this->authUser()->id)
        //     ->latest()
        //     ->groupBy('course_id')
        //     ->with(['course' => fn($course) => $course->select(['id', 'title'])])
        //     ->has('course')->get();

        $completedCourses = StudentCompleteCourse::where('user_id', $this->authUser()->id)
        ->whereIn('id', function ($q) {
            $q->selectRaw('MAX(id)')
            ->from('student_complete_courses as s2')
            ->whereColumn('s2.course_id', 'student_complete_courses.course_id')
            ->where('s2.user_id', $this->authUser()->id)
            ->whereNull('s2.deleted_at');
        })
        ->with(['course:id,title'])
        ->orderByDesc('created_at')
        ->get();

        // $completedCourses = User::with([
        //     'enrollments' => function ($enrollments) {
        //         return $enrollments->with([
        //             'course' => function ($course) {
        //                 $course->whereRelation('completed_course', 'user_id', $this->authUser()->id)
        //                 ->with(['completed_course' => function($query){
        //                     $query->where('user_id', $this->authUser()->id)->first();
        //                 }]);
        //             }
        //         ]);
        //     }
        // ])
        //     ->where('id', $this->authUser()->id)
        //     ->first();

        return $this->sendResponse($completedCourses, 'List of all completed courses.', 200);
    }

    public function getCourseCategories()
    {
        $classes = ClassCategory::select(['id', 'class_name', 'class_code'])->get();

        return $this->sendResponse($classes, 'List of all course categories', 200);
    }

    public function setLog(Request $request)
    {
        try {
            $course = Course::where('uuid', $request->course_uuid)->first();
            $user = User::where('id', $this->authUser()->id)->first();

            if($course) {
                $log = Activity::where('log_name', 'user_course_activity')
                    ->where('causer_id', $user->id)
                    ->where('properties->course_id', $course->id)
                    ->latest()
                    ->first();
    
                if($log) {
                    activity('user_course_activity')
                    ->causedBy($user)
                    ->withProperties([
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'course_id' => $course->id,
                    ])
                    ->event('created')
                    ->performedOn($user)
                    ->log('Course Continued');
                } else {
                    activity('user_course_activity')
                    ->causedBy($user)
                    ->withProperties([
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'course_id' => $course->id,
                    ])
                    ->event('created')
                    ->performedOn($user)
                    ->log($request->status);
                }
            }
        } catch(\Exception $e) {

            Log::error('User Course Log' . $e);
        }

        return $this->sendResponse([], 'Logged!', 200);
    }

    public function getCourseZoomMeetings($id)
    {
        $zoom_meetings = ZoomMeeting::where('course_id', $id)->with(['attendee' => function($query) {
            $query->with(['zoom_meeting_activity', 'user' => fn($query) => $query->select(['id', 'first_name', 'last_name', 'suffix', 'email'])]);
        }])->latest()->get();

        return $this->sendResponse($zoom_meetings, 'Course zoom meetings', 200);
    }

    public function getMyCourseEvaluations($uuid)
    {
        $course = Course::where('uuid', $uuid)->with(['course_evaluations' => function ($course_evaluations) {
            return $this->getCourseEvaluations($course_evaluations);
        }])->select([
            'id',
            'type_of_learning',
            'title',
            'training_hours',
            'facilitator',
            'description',
            'uploader_id',
            'uuid'
        ])->first();
        
        return $this->sendResponse($course, 'Course Evaluations', 200);

    }

    public function checkScormProgress(Request $request)
    {
        $request->validate([
        'registration_id' => 'required|exists:scorm_registrations,id'
        ]);

        $scorm_registration = ScormRegistration::where('id', $request->registration_id)->with('scorm')->first();

        $result = ScormCloudService::getScormRegistrationProgress($scorm_registration->registration_id);

        $registration_progress = $result['registration_progress'];
        
        $registration_progress_encoded = json_encode($registration_progress);
        $registration_progress_activity_details_encoded = json_encode($registration_progress['activityDetails']);

        if($result['result'] === 'success') {
            $scorm_registration_progress = ScormRegistrationProgress::updateOrCreate([
                'scorm_registration_id' => $request->registration_id,
                'user_id' => $this->authUser()->id
            ], [
                'completion_status' => $registration_progress['registrationCompletion'],
                'success_status' => $registration_progress['registrationSuccess'],
                'completion_date' => $registration_progress['registrationCompletion'] == ScormRegistrationProgress::IS_COMPLETED ? $registration_progress['completedDate'] : null,
                'total_seconds_tracked' => $registration_progress['totalSecondsTracked'],
                'first_accessed_date' => $registration_progress['firstAccessDate'] ?? null,
                'last_accessed_date' => $registration_progress['lastAccessDate'] ?? null,
                'activity_details' => $registration_progress_activity_details_encoded ?? null,
                'data' => $registration_progress_encoded,
            ]);

            $scorm_registration->update([
                'scorm_registration_progress_id' => $scorm_registration_progress['id']
            ]);
            
            $scorm_registration->save();

            foreach($registration_progress['activityDetails']['children'] as $scorm_children) {

                Log::info(json_encode($scorm_children));

                foreach($scorm_children['runtime']['runtimeInteractions'] as $runtime) {
                    $runtime_interactions_encoded = json_encode($runtime);

                    $type = (substr($runtime['id'], 0, strlen('RISESTORYLINE')) === 'RISESTORYLINE' || substr($runtime['id'], 0, strlen('STORYLINE360')) === 'STORYLINE360') ? 'RISESTORYLINE' : 'RISE';
                    
                    if(substr($runtime['id'], 0, strlen('Scene')) === 'Scene') {
                        $type = 'STORYLINE360';
                    }
                    //catch storyline multiple attempts

                    // if($type === 'STORYLINE') {
                    //     $runtime['id'] = str_replace(' ', '_', $runtime['id'].'__0'); //append attempt count to replicate rise 
                    // }

                    if($type === 'RISE') {
                        ScormRegistrationInteraction::updateOrCreate([
                            'scorm_registration_id' => $request->registration_id,
                            'user_id' => $this->authUser()->id,
                            'type' => $type,
                            'interaction_id' => $runtime['id']
                        ], [
                            'interaction_type' => strtoupper($runtime['type']) ?? 'LONGFILLIN',
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
                        'scorm_registration_id' => $request->registration_id,
                        'user_id' => $this->authUser()->id,
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

            if($registration_progress['registrationCompletion'] === 'COMPLETED') {
                StudentModuleProgress::firstOrCreate([
                    'course_id' => $scorm_registration->scorm->course_id,
                    'user_id' => $this->authUser()->id,
                    'model_id' => $scorm_registration->scorm->model_id,
                    'model_type' => $scorm_registration->scorm->model_type,
                ]);
            }
        }

        $modules = Module::select(['id', 'course_id'])->where('course_id', $scorm_registration->scorm->course_id)->get();

        return $this->sendResponse(['completion_status' => $registration_progress['registrationCompletion'], 'modules' => $modules], 'Update Registration Progress', 201);
    }

    
    public function courseExport($course_id)
    {
        $course = Course::with(['modules' => fn($query) => $query->with(['topics' => fn($query) => $query->select(['id', 'title', 'module_id', 'course_id'])->with(['sub_topics' => fn($query) => $query->select(['id', 'title', 'topic_id', 'course_id'])])])])->find($course_id);

        if (!$course) {
            return $this->sendResponse([], 'No course found.', 404);
        }

        $custom_module_fields = CustomModuleField::select(['input_field_name', 'input_field_label', 'input_field_type'])->get();

        Settings::loadConfig();
        Settings::setZipClass(Settings::PCLZIP);

        $phpWord = new PhpWord();
        $headerStyle = ['size' => 14, 'bold' => true];
        $subHeaderStyle = ['size' => 13, 'bold' => true];

        $section = $phpWord->addSection();
        $section->addText("Title: " . $course->title, $headerStyle);
        $section->addText("Course Code: " . $course->course_code);
        $section->addText("Training hours: " . $course->training_hours);
        $section->addText("Facilitator: " . $course->facilitator);
        $date_conducted = '';

        if (isset($course->available_until['start_date'], $course->available_until['end_date'])) {
            $startDate = strtotime($course->available_until['start_date']);
            $endDate = strtotime($course->available_until['end_date']);

            if (date('Y', $startDate) == date('Y', $endDate)) {
                if ($course->available_until['start_date'] == $course->available_until['end_date']) {
                    $date_conducted = date('F d, Y', $endDate);
                } else {
                    $date_conducted = date('F d', $startDate) . ' - ' . date('F d, Y', $endDate);
                }
            } else {
                $date_conducted = date('F d, Y', $startDate) . ' - ' . date('F d, Y', $endDate);
            }
        }
        $section->addText("Conduct dates: " . $date_conducted);
        $section->addText("Training Credits: " . $course->training_credits);
        $section->addText("Type of Learning: " . $course->type_of_learning);

        if ($course->description) {
            $html = $course->description;
            $html = str_replace('text-align: justify;', 'text-align: left;', $html);
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, true);
        }

        $section->addPageBreak();
        $section->addText("Modules", $headerStyle);
        $section->addTextBreak();

        foreach ($course->modules as $module) {
            $section->addText("Title: $module->title", $subHeaderStyle);
            $section->addText("Description: " . $module->description);
            $section->addText("Contact Person: " . ($module->contact_person ?: 'N/A'));
            $section->addText("Version: " . ($module->version ?: 'N/A'));
            $section->addText("Language: " . (!empty($module->language) && is_array($module->language)
                ? implode(', ', $module->language) : 'N/A'));

            if($module->data) {
                foreach($custom_module_fields as $custom_module_field) {
                    if($module->data[$custom_module_field->input_field_name]){
                        if($custom_module_field->input_field_type == CustomModuleField::TYPE_DROPDOWN) {
                            $section->addText($custom_module_field->input_field_label .": " . (!empty($module->data[$custom_module_field->input_field_name]) && is_array($module->data[$custom_module_field->input_field_name]) ? implode(', ', $module->data[$custom_module_field->input_field_name]) : 'N/A'));
                        } else {
                            $section->addText($custom_module_field->input_field_label .": " . ($module->data[$custom_module_field->input_field_name] ?: 'N/A'));
                        }
                    }
                }
            }

            if (!empty($module->topics)) {
                $section->addText("Topics: ");
                foreach ($module->topics as $topicIndex => $topic) {
                    $topicNumber = $topicIndex + 1;
                    $section->addText("$topicNumber. " . $topic->title);

                    if (!empty($topic->sub_topics)) {
                        foreach ($topic->sub_topics as $subTopicIndex => $sub_topic) {
                            $subTopicNumber = $topicNumber . '.' . ($subTopicIndex + 1);
                            $section->addListItem("$subTopicNumber. " . $sub_topic->title, 1);
                        }
                    }
                }
            }

            $section->addTextBreak();
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        $fileName = 'course-' . Str::limit(Str::slug($course->title, '_'), 50, '') . '.docx';
        Storage::disk('public')->put($fileName, file_get_contents($tempFile));

        unlink($tempFile);

        return response()->download(Storage::disk('public')->path($fileName))->deleteFileAfterSend(true);
    }

}
