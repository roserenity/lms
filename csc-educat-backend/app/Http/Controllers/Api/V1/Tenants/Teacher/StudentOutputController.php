<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use App\Exports\Tenants\Teacher\CourseEnrolleesExport;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Tenants\Teacher\StudentCourseProgressResource;
use App\Http\Resources\Tenants\Teacher\StudentOverallGradingResource;
use App\Http\Resources\Tenants\Teacher\StudentCourseProgressDetailsResource;

use App\Models\ClassCategory;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\StudentCourseProgress;
use App\Models\StudentModuleProgress;
use App\Models\Topic;
use App\Models\Subtopic;
use App\Models\User;
use App\Models\AssessmentScore;
use App\Models\Assessment;
use App\Models\ZoomMeeting;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use Log;

class StudentOutputController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function enrollees(Request $request, $course_uuid)
    {
        $searchParts = explode(' ', request('search'));

        $course = Course::select(['id', 'uuid', 'title'])->where('uuid', $course_uuid)->first();

        $users = User::select('email', 'id', 'first_name', 'last_name', 'middle_name', 'suffix')
            ->whereHas('enrollments', function ($query) use ($course){
                $query->where('course_id', $course->id);
            })
            ->when(!empty(request('search')), function ($query) use ($searchParts) {
                Helper::queryNameEmailSearch($searchParts, $query);
            })
            ->with(['course_progress' => function ($query) use ($course){
                $query->where('course_id', $course->id);
            }])
            ->paginate($request->paginate);

        // foreach ($users as $user) {
        //     if ($user->course_progress->isEmpty()) {
        //         $progress = $user->course_progress()->create([
        //             'course_id' => $course->id,
        //             'total_done_topics' => 0,
        //             'total_done_sub_topics' => 0,
        //             'total_done_assessments' => 0,
        //             'total_done_course_evaluations' => 0,
        //             'total_done_module_evaluations' => 0,
        //             'total_done_group_activities' => 0,
        //             'total_attended_zoom_meetings' => 0,
        //             'total_time_spent' => 0,
        //             'progress' => 0,
        //         ]);
        
        //         $user->setRelation('course_progress', collect([$progress]));
        //     } else {
                
        //         $learner_data = $user->course_progress->first();

        //         $progress = $course->type_of_learning === 'BLENDED' ? Helper::calculateLearnerProgressPercentBlended($learner_data) : Helper::calculateLearnerProgressPercentElearning($learner_data);

        //         Log::info($progress);
                
        //         $course_progress = StudentCourseProgress::updateOrCreate(
        //             [
        //                 'user_id' => $user->id,
        //                 'course_id' => $course->id
        //             ],
        //             array_merge($learner_data->toArray(), ['progress' => $progress])
        //         );

        //         $user->setRelation('course_progress', collect([$course_progress]));

        //     }
        // }

        return $this->sendResponse([ 'title' => $course->title, 'users' => $users], "List of enrollees", 200);
    }

    public function exportEnrollees(Request $request, $course_uuid)
    {
        return new CourseEnrolleesExport($request->search, $course_uuid);
    }

    public function enrolleeProgress ($course_uuid, $user_id)
    {
        $user = User::select(['id', 'first_name', 'last_name', 'suffix', 'email'])->where('id', $user_id)->get();

        $course = Course::where('uuid', $course_uuid)
            ->select(['id', 'title', 'type_of_learning'])
            ->withCount(['assessments', 'topics', 'sub_topics', 'course_evaluations', 'module_evaluations', 'zoom_meetings', 'group_activities'])
            ->withCount([
                'my_assessment_score as total_checked_assessments' => function ($query) {
                    $query->where('checked', true);
                }
            ])
            ->first()
            ->makeHidden([
                'course_card',
                'image',
                'course_is_open_for_registration',
                'module_evaluations',
                'group_activities',
                'user_is_enrolled'
            ]);

        $course = Helper::setLearnerCourseData($course, $user_id);
        $course['total_checked_assessments'] = $course->total_checked_assessments ?? 0;

        $assessments = Assessment::with([
            'assessment_scores' => fn ($score) => $score->with('user')
              ->where('active', 1)
              ->where('user_id', $user_id)->latest()->get(),
            ])
            ->withCount('assessment_questions')
            ->withSum('assessment_questions', 'score')

            ->where('course_id', $course->id)
            ->get();
        
        $score = 0;
        $assessmentQuestionsSumScore = 0;
        foreach ($assessments as $assessment) {
            $assessmentQuestionsSumScore += (float) $assessment['total_points'];
            if (!empty($assessment['assessment_scores']) && isset($assessment['assessment_scores'][0]['score'])) {
                $score += (float) $assessment['assessment_scores'][0]['score'];
            }
        }
        
        return $this->sendResponse(['user' => $user, 'progress' => $course, 'assessment_questions_sum' => $assessmentQuestionsSumScore, 'score' => $score], "Student Progress", 200);
    }

    public function enrolleeProgressZoomMeeting($course_uuid, $user_id)
    {

        $course = Course::select(['id'])->where('uuid', $course_uuid)->first();

        if(!$course) {
            return $this->sendResponse([], 'Course not found.', 404);
        }

        $user = User::find($user_id);

        if(!$user) {
            return $this->sendResponse([], 'User not found.', 404);
        }

        $zoom_meetings = ZoomMeeting::where('course_id', $course->id)
            ->with(['attendees' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id)->with(['zoom_meeting_activity', 'user']);
            }])
            ->latest()->get();

        return $this->sendResponse($zoom_meetings, "Here is the student's zoom meeting progress", 200);
    }

    public function enrolleeProgressCourse($course_uuid, $user_id)
    {
        $selected = Course::where('uuid', $course_uuid)->first();

        $course = Course::with([
            'modules' => function ($modules) use ($user_id) {
                return $modules
                    ->select(['id', 'title', 'course_id', 'is_certificate_open'])
                    ->with([
                        'module_content' => function ($query) use ($user_id) {
                            $query->with([
                                'contentable' => function ($morphTo) use ($user_id)  {
                                    $morphTo->morphWith([
                                        \App\Models\Topic::class => [
                                                'learning_material',
                                                'topic_has_assessments' => fn($topic_has_assessments) => $topic_has_assessments
                                                    ->select([
                                                        'id',
                                                        'uuid',
                                                        'module_id',
                                                        'title',
                                                        'total_points'
                                                    ])->withSum('assessment_questions', 'score')->with([
                                                    'assessment_scores' => fn ($assessment_score) => $assessment_score
                                                        ->where('active', 1)
                                                        ->where('user_id', $user_id)->latest()->get(),
                                                ]),
                                                'sub_topics' => function ($sub_topics) use ($user_id) {
                                                    $sub_topics->with([
                                                        'learning_material',
                                                        'sub_topic_has_assessments' => function($sub_topic_has_assessments) use ($user_id) {
                                                            $sub_topic_has_assessments->withSum('assessment_questions', 'score')->with([
                                                                'assessment_scores' => function ($assessment_score) use ($user_id) {
                                                                    $assessment_score ->where('active', 1)->where('user_id', $user_id)->latest()->get();
                                                                }
                                                            ]);
                                                        },
                                                    ]);
                                                
                                                    $sub_topics->orderBy('sort_no', 'ASC')
                                                        ->select([
                                                            'id',
                                                            'uuid',
                                                            'course_id',
                                                            'topic_id',
                                                            'title'
                                                        ]);
                                    
                                                },
                          
                                        ],
                                        \App\Models\Assessment::class => [
                                            'assessment_scores' => function ($query) use ($user_id) {
                                                $query->where('active', 1)
                                                    ->where('user_id', $user_id)
                                                    ->latest();
                                            },
                                        ],
                                    ]);
                                }
                            ]);
                        },
                        'module_evaluations'  => fn ($query) => $query->with(['student_module_evaluation_progress' => fn($query) => $query->where('user_id', $user_id)]),
                    ])->selectAttributes();
                },
            'course_evaluations' => fn ($query) => $query->with(['student_course_evaluation_progress' => fn($query) => $query->where('user_id', $user_id)])
        ])->select([
            'id',
            'type_of_learning',
            'title',
            'uuid',
        ])
        ->where('uuid', $course_uuid)
        ->first()
        ->makeHidden([
            'course_card',
            'assessments',
            'course_is_open_for_registration',
            'user_is_enrolled'
        ]);

        $course->modules->makeHidden([
            'module_assessment_is_done',
            'module_evaluation_is_done',
            'module_is_locked',
            /* 'module_is_prerequisite', */
            'module_subtopic_is_done',
            'module_status',
            'prerequisites'
        ]);
        
        foreach ($course->modules as $module) {
            foreach ($module->module_content as $module_content) {
                if ($module_content->contentable_type === \App\Models\Topic::class) {
                    if($module_content->contentable) {
                        $module_content->contentable['student_is_done'] = $module_content->contentable->getStudentTopicStatus($user_id);
                        $module_content->contentable['student_topic_progress'] = $module_content->contentable->getStudentTopicProgress($user_id);
                        foreach ($module_content->contentable->topic_has_assessments as $topic_assessment) {
                            $topic_assessment['student_is_done'] = $topic_assessment->getStudentAssessmentStatus($user_id);
                            $topic_assessment['student_assessment_progress'] = $topic_assessment->getStudentAssessmentProgress($user_id);
                        }
        
                        foreach ($module_content->contentable->sub_topics as $sub_topic) {
                            $sub_topic['student_is_done'] = $sub_topic->getStudentSubTopicStatus($user_id);
                            $sub_topic['student_sub_topic_progress'] = $sub_topic->getStudentSubtopicProgress($user_id);
                            foreach ($sub_topic->sub_topic_has_assessments as $sub_topic_assessment) {
                                $sub_topic_assessment['student_is_done'] = $sub_topic_assessment->getStudentAssessmentStatus($user_id);
                                $sub_topic_assessment['student_assessment_progress'] = $sub_topic_assessment->getStudentAssessmentProgress($user_id);
                            }
                        }
                    }
                }
                if ($module_content->contentable_type === \App\Models\Assessment::class) {
                    if($module_content->contentable) {
                        $module_content->contentable['student_is_done'] = $module_content->contentable->getStudentAssessmentStatus($user_id);
                        $module_content->contentable['student_assessment_progress'] = $module_content->contentable->getStudentAssessmentProgress($user_id);
                    }
                }
                if ($module_content->contentable_type === \App\Models\GroupActivity::class) {
                    if($module_content->contentable) {
                        $module_content->contentable['student_is_done'] = $module_content->contentable->getStudentGroupActivityStatus($user_id);
                        $module_content->contentable['student_group_activity_progress'] = $module_content->contentable->getStudentGroupActivityProgress($user_id);
                        $module_content->contentable['student_group_activity_status_check'] = $module_content->contentable->getStudentGroupActivityGroup($user_id);
                    }
                }
            }
        }

        foreach ($course->course_evaluations as $course_evaluation) {
            $course_evaluation['student_is_done'] = $course_evaluation->getStudentCourseEvaluationStatus($user_id);
            $course_evaluation['student_course_evaluation_progress'] = $course_evaluation->getStudentCourseEvaluationProgress($user_id);
        }

        return $this->sendResponse($course, "Here is the student's course progress", 200);
    }

    public function enrolleeProgressCourses(Request $request, $user_id)
    {
        $enrollments = Enrollment::with([
            'course' => function ($query) use ($request, $user_id) {
                $query->select(['id', 'title', 'type_of_learning', 'uuid'])
                      ->withCount(['assessments', 'topics', 'sub_topics', 'course_evaluations', 'module_evaluations', 'zoom_meetings'])
                      ->withCount([
                          'my_assessment_score as total_checked_assessments' => function ($query) {
                              $query->where('checked', true);
                          }
                        ]);
                }
            ])
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('course', function ($subQuery) use ($request) {
                    $subQuery->where('title', 'LIKE', '%' . $request->search . '%');
                });
            })
            ->where('user_id', $user_id)
            ->paginate($request->paginate);   
        
        foreach ($enrollments as $enrollment) {
            $enrollment->course->makeHidden([
                'course_card',
                'image',
                'course_is_open_for_registration',
                'module_evaluations',
                'user_is_enrolled'
            ]);
        
            $course = $enrollment->course; // Access the associated course
        
            // Access properties and call methods on the associated course
            $course = Helper::setLearnerCourseData($course, $user_id);
            $course['total_checked_assessments'] = $course->total_checked_assessments ?? 0;

            $assessments = Assessment::with([
                'assessment_scores' => fn ($score) => $score->with('user')
                  ->where('active', 1)
                  ->where('user_id', $user_id)->first(),
                ])
                ->withCount('assessment_questions')
                ->withSum('assessment_questions', 'score')
    
                ->where('course_id', $course->id)
                ->get();

            $score = 0;
            $assessmentQuestionsSumScore = 0;
            
            foreach ($assessments as $assessment) {
                $assessmentQuestionsSumScore += (float) $assessment['total_points'];
                if (!empty($assessment['assessment_scores']) && isset($assessment['assessment_scores'][0]['score'])) {
                    $score += (float) $assessment['assessment_scores'][0]['score'];
                }
            }

            $course['score'] = $score;
            $course['assessment_questions_sum'] = $assessmentQuestionsSumScore;
        }
            
        
        return $this->sendResponse($enrollments, "Here is the student's courses progress", 200);
    }


    //unused 
    public function studentInfo($user_uuid)
    {

        $user = User::with('enrollments')
            ->where('uuid', $user_uuid)
            ->first();

        if (!$user) {
            return $this->sendResponse([], 'No user found.', 404);
        }

        $class_categories = ClassCategory::whereHas('courses', function (Builder $query) use ($user) {
            $query->whereIn('course_id', $user->enrollments->pluck('course_id'));
        })
            ->get();

        $user->class_categories = $class_categories ?? null;

        return $this->sendResponse($user, "Here is the student's info", 200);
    }

    public function classCategories()
    {
        $class_categories = collect();

        $courses = Course::where('uploader_id', $this->authUser()->id)->with('class_categories')->get()->pluck('class_categories');

        foreach ($courses as $course) {
            $class_categories = $class_categories->concat($course);
        }

        return $this->sendResponse($class_categories, 'List of class categories.', 200);
    }

    public function courses(Request $request)
    {
        $courses = Course::select(['id', 'status', 'title', 'uuid', 'created_at', 'type_of_learning', 'available_until', 'course_code'])
            ->with([
                'class_categories'
            ])
            ->when($request->search, function ($query) use ($request) {
                FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
            })
            ->when(($request->created_from || $request->created_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->created_from, $request->created_to);
            })
            ->when($request->status, function($query) use ($request){
                $query->where('status', $request->status);
            })
            ->when($request->type_of_learning, function($query) use ($request){
                $query->where('type_of_learning', $request->type_of_learning);
            })
            // ->where(function ($query) {
            //     $query->where('uploader_id', $this->authUser()->id)
            //         ->orWhereHas('shared_to_users', function ($subquery) {
            //             $subquery->where('user_id', $this->authUser()->id);
            //         });
            // })
            ->withCount('enrollments')
            ->latest()
            ->paginate($request->paginate);

            $courses->makeHidden([
                'user_is_enrolled',
                'course_card',
                'course_is_open_for_registration']);

        return $this->sendResponse($courses, 'List of student output courses.', 200);
    }

    public function modules(Request $request)
    {
        // $modules = Course::when($request->course_uuid, function ($query) use ($request) {
        //     $query->where('uuid', $request->course_uuid);
        // })
        //     ->with('modules')
        //     ->get();

        // $users = User::whereRelation('enrollment', 'class_category_id', $request->class_category_id)
        //     ->with([
        //         'enrollment' => function ($enrollment) use ($request) {
        //             return $enrollment->with([
        //                 'class_category' => function ($class_category) use ($request) {
        //                     $class_category->with([
        //                         'courses' => function ($courses) use ($request) {
        //                             return $courses->where('uuid', $request->course_uuid)
        //                                 ->with([
        //                                     'modules' => function ($modules) {
        //                                         return $modules->with('my_assessment_score')
        //                                             ->withSum('assessment_score as assessment_overall_score', 'score');
        //                                     }
        //                                 ]);
        //                         }
        //                     ]);
        //                 }
        //             ]);
        //         },
        //     ])
        //     ->get();

        $users = User::has('enrollments')
            ->with([
                'enrollments' => function ($enrollments) use ($request) {
                    return $enrollments->with([
                        'course' => function ($course) use ($request) {
                            return $course->whereRelation('class_categories', 'id', $request->class_category_id)
                                ->where('uuid', $request->course_uuid)
                                ->with([
                                    'modules' => function ($modules) {
                                        return $modules->with('my_assessment_score')
                                            ->withSum('assessment_score as assessment_overall_score', 'score');
                                    }
                                ]);
                        }
                    ]);
                }
            ])
            ->get();

        return $this->sendResponse($users, 'List of modules.', 200);
    }

    public function assessments(Request $request)
    {
        // $assessments = Module::when($request->module_uuid, function ($query) use ($request) {
        //     $query->where('uuid', $request->module_uuid);
        // })
        //     ->with('assessments')
        //     ->get();

        // $users = User::whereRelation('enrollment', 'class_category_id', $request->class_category_id)
        //     ->with([
        //         'enrollment' => function ($enrollment) use ($request) {
        //             return $enrollment->with([
        //                 'class_category' => function ($class_category) use ($request) {
        //                     $class_category->with([
        //                         'courses' => function ($courses) use ($request) {
        //                             return $courses->where('uuid', $request->course_uuid)
        //                                 ->with([
        //                                     'modules' => function ($modules) use ($request) {
        //                                         return $modules->where('uuid', $request->module_uuid)
        //                                             ->with([
        //                                                 'assessments' => function ($assessments) {
        //                                                     return $assessments->with('assessment_scores')
        //                                                         ->withSum('assessment_questions as assessment_overall_score', 'score');
        //                                                 }
        //                                             ]);
        //                                     }
        //                                 ]);
        //                         }
        //                     ]);
        //                 }
        //             ]);
        //         },
        //     ])
        //     ->get();

        $users = User::has('enrollments')
            ->with([
                'enrollments' => function ($enrollments) use ($request) {
                    return $enrollments->with([
                        'course' => function ($course) use ($request) {
                            return $course->whereRelation('class_categories', 'id', $request->class_category_id)
                                ->where('uuid', $request->course_uuid)
                                ->with([
                                    'modules' => function ($modules) use ($request) {
                                        return $modules->where('uuid', $request->module_uuid)
                                            ->with([
                                                'assessments' => function ($assessments) {
                                                    return $assessments->with('assessment_scores')
                                                        ->withSum('assessment_questions as assessment_overall_score', 'score');
                                                }
                                            ]);
                                    }
                                ]);
                        }
                    ]);
                }
            ])
            ->get();


        return $this->sendResponse($users, 'List of assessments.', 200);
    }

    public function studentRecords(Request $request)
    {
        // $users = User::whereRelation('enrollment', 'class_category_id', $request->class_category_id)
        //     ->with([
        //         'enrollment' => function ($enrollment) {
        //             return $enrollment->with([
        //                 'class_category' => function ($class_category) {
        //                     $class_category->with([
        //                         'courses' => function ($courses) {
        //                             return $courses->with('my_assessment_score')
        //                                 ->withSum('assessment_score as overall_assessment_score', 'score');
        //                         }
        //                     ]);
        //                 }
        //             ]);
        //         },
        //     ])
        //     ->get();

        $users = User::has('enrollments')
            ->with([
                'enrollments' => function ($enrollments) use ($request) {
                    return $enrollments->with([
                        'course' => function ($course) use ($request) {
                            return $course->whereRelation('class_categories', 'id', $request->class_category_id)
                                ->with('my_assessment_score')
                                ->withSum('assessment_score as overall_assessment_score', 'score');
                        }
                    ]);
                }
            ])
            ->get();

        return $this->sendResponse($users, 'List of records.', 200);
    }

    public function showStudentOverallGrading($user_uuid, Request $request)
    {
        $user = User::where('uuid', $user_uuid)->firstOrFail();

        $enrolled = Enrollment::where('user_id', $user->id)->pluck('course_id');

        $courses = Course::whereIn('id', $enrolled)
            ->whereHas('class_categories', function (Builder $query) use ($request) {
                $query->where('id', $request->class_category_id);
            })
            ->where('status', Course::IS_PUBLISH)
            ->withCount(['assessments', 'topics', 'sub_topics'])
            ->get();

        return $this->sendResponse(StudentOverallGradingResource::collection($courses), 'Student overall grading.', 200);
    }

    public function showStudentCourseProgress($user_uuid, Request $request)
    {
        // $user = User::where('uuid', $user_uuid)->firstOrFail();

        // $enrolled = Enrollment::where('user_id', $user->id)->first('class_category_id');

        // $courses = Course::whereHas('class_categories', function (Builder $query) use ($enrolled) {
        //     $query->where('id', $enrolled->class_category_id);
        // })
        //     ->where('status', Course::IS_PUBLISH)
        //     ->withCount(['assessments', 'topics', 'sub_topics'])
        //     ->get();

        // return $this->sendResponse(StudentCourseProgressResource::collection($courses), 'Student record.', 200);

        $user = User::where('uuid', $user_uuid)->firstOrFail();

        $enrolled = Enrollment::where('user_id', $user->id)->pluck('course_id');

        $courses = Course::whereIn('id', $enrolled)
            ->whereHas('class_categories', function (Builder $query) use ($request) {
                $query->where('id', $request->class_category_id);
            })
            ->where('status', Course::IS_PUBLISH)
            ->withCount(['assessments', 'topics', 'sub_topics'])
            ->get();

        return $this->sendResponse(StudentCourseProgressResource::collection($courses), 'Student record.', 200);
    }

    public function showStudentCourseProgressDetails($course_uuid, $user_uuid)
    {
        $user = User::where('uuid', $user_uuid)->first();

        $course = Course::with([
            'modules' => fn ($modules) => $modules->with([
                'prerequisites',
                'topics' => fn ($topics) => $topics->with([
                    'topic_has_assessments',
                    'learning_material',
                    'sub_topics' => fn ($sub_topics) => $sub_topics->with([
                        'sub_topic_has_assessments',
                        'learning_material'
                    ])
                ])
            ]),
            'assessments',
            'uploader',
            'announcements' => fn ($announcements) => $announcements->with([
                'comments' => fn ($comments) => $comments->with('replies')
            ])
            // 'announcements'
        ])
            ->withCount(['assessments', 'topics', 'sub_topics'])
            ->where('uuid', $course_uuid)
            ->first();

        foreach ($course->modules as $module) {
            if ($module['topics']) {
                foreach ($module->topics as $topic) {
                    if ($topic['topic_has_assessments']) {
                        foreach ($topic->topic_has_assessments as $assessment) {
                            $assessment_scores = AssessmentScore::where('assessment_id', $assessment->id)
                                ->where('user_id', $user->id);
                            
                            $score = $assessment_scores->where('active', 1)->first();

                            // if ($assessment->accepted_score == 'highest') $score = $assessment_scores->orderBy('score', 'desc')->first();
                            // if ($assessment->accepted_score == 'latest' || !$assessment->accepted_score) $score = $assessment_scores->latest()->first();
                            // if($assessment->accepted_score == 'average') $score = $assessment_scores->latest()->first();
                            $assessment['assessment_score'] = [
                                'score' =>  $score->score ?? null,
                                'attempts' => $assessment_scores->count(),
                                'created_at' => $score->created_at ?? null,
                            ];
                        }
                    }

                    if ($topic['sub_topics']) {
                        foreach ($topic->sub_topics as $sub_topic) {
                            if ($sub_topic['sub_topic_has_assessments']) {
                                foreach ($sub_topic->sub_topic_has_assessments as $assessment) {
                                    $assessment_scores = AssessmentScore::where('assessment_id', $assessment->id)
                                        ->where('user_id', $user->id);

                                    if ($assessment->accepted_score == 'highest') $score = $assessment_scores->orderBy('score', 'desc')->first();
                                    if ($assessment->accepted_score == 'latest' || !$assessment->accepted_score) $score = $assessment_scores->latest()->first();
                                    // if($assessment->accepted_score == 'average') $score = $assessment_scores->latest()->first();
                                    $assessment['assessment_score'] = [
                                        'score' =>  $score->score ?? null,
                                        'attempts' => $assessment_scores->count(),
                                        'created_at' => $score->created_at ?? null,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->sendResponse(new StudentCourseProgressDetailsResource($course), 'Show course progress details.', 200);
        // return $this->sendResponse($course, 'Show course progress details.', 200);
    }

    public function markAsCompletedEnrolleeTopic($user_id, $topic_id) {

        $topic = Topic::findOrFail($topic_id);

        StudentModuleProgress::firstOrCreate([
            'course_id' => $topic->course_id,
            'user_id' => $user_id,
            'model_id' => $topic_id,
            'model_type' => 'App\\Models\\Topic',
            'status' => StudentModuleProgress::STATUS_DONE
        ]);

        $course = Course::where('id', $topic->course_id)
            ->withCount(['assessments', 'topics', 'sub_topics', 'course_evaluations', 'module_evaluations', 'zoom_meetings', 'group_activities'])
            ->withCount([
                'my_assessment_score as total_checked_assessments' => function ($query) {
                    $query->where('checked', true);
                }
            ])
            ->first();
        
        if (!$course) {
            return $this->sendResponse([], 'No course found.', 404);
        }
        
        $progress = [
            'total_checked_assessments' => $course->total_checked_assessments ?? 0,
            'total_done_topics' => $course->getTotalDoneTopics($user_id),
            'total_done_sub_topics' => $course->getTotalDoneSubTopics($user_id),
            'total_done_assessments' => $course->getTotalDoneAssessments($user_id),
            'total_done_course_evaluations' => $course->getTotalDoneCourseEvaluations($user_id),
            'total_done_module_evaluations' => $course->getTotalDoneModuleEvaluations($user_id),
            'total_attended_zoom_meetings' => $course->getTotalAttendedZoomMeetings($user_id),
            'total_done_group_activities' => $course->getTotalDoneGroupActivities($user_id),
            'total_time_spent' => $course->getTotalTimeSpent($course->id, $user_id),
            'topics_count' => $course->topics_count,
            'sub_topics_count' => $course->sub_topics_count,
            'assessments_count' => $course->assessments_count,
            'course_evaluations_count' => $course->course_evaluations_count,
            'module_evaluations_count' => $course->module_evaluations_count,
            'zoom_meetings_count' => $course->zoom_meetings_count,
            'group_activities_count' => $course->group_activities_count,
        ];

        $progress_percent = $course->type_of_learning === 'BLENDED' ? Helper::calculateLearnerProgressPercentBlended((object)$progress) : Helper::calculateLearnerProgressPercentElearning((object)$progress);

        StudentCourseProgress::updateOrCreate([
            'user_id' => $user_id,
            'course_id' => $topic->course_id
        ], [
            ... $progress,
            'progress' => $progress_percent
        ]);

        return $this->sendResponse([], 'List of records.', 200);

    }

    public function markAsCompletedtopicEnrolleeSubtopic($user_id, $subtopic_id) {

        $subtopic = Subtopic::findOrFail($subtopic_id);

        StudentModuleProgress::firstOrCreate([
            'course_id' => $subtopic->course_id,
            'user_id' => $user_id,
            'model_id' => $subtopic_id,
            'model_type' => 'App\\Models\\SubTopic',
            'status' => StudentModuleProgress::STATUS_DONE
        ]);

        $course = Course::where('id', $subtopic->course_id)
            ->withCount(['assessments', 'topics', 'sub_topics', 'course_evaluations', 'module_evaluations', 'zoom_meetings', 'group_activities'])
            ->withCount([
                'my_assessment_score as total_checked_assessments' => function ($query) {
                    $query->where('checked', true);
                }
            ])
            ->first();
        
        if (!$course) {
            return $this->sendResponse([], 'No course found.', 404);
        }
        
        $progress = [
            'total_checked_assessments' => $course->total_checked_assessments ?? 0,
            'total_done_topics' => $course->getTotalDoneTopics($user_id),
            'total_done_sub_topics' => $course->getTotalDoneSubTopics($user_id),
            'total_done_assessments' => $course->getTotalDoneAssessments($user_id),
            'total_done_course_evaluations' => $course->getTotalDoneCourseEvaluations($user_id),
            'total_done_module_evaluations' => $course->getTotalDoneModuleEvaluations($user_id),
            'total_attended_zoom_meetings' => $course->getTotalAttendedZoomMeetings($user_id),
            'total_done_group_activities' => $course->getTotalDoneGroupActivities($user_id),
            'total_time_spent' => $course->getTotalTimeSpent($course->id, $user_id),
            'topics_count' => $course->topics_count,
            'sub_topics_count' => $course->sub_topics_count,
            'assessments_count' => $course->assessments_count,
            'course_evaluations_count' => $course->course_evaluations_count,
            'module_evaluations_count' => $course->module_evaluations_count,
            'zoom_meetings_count' => $course->zoom_meetings_count,
            'group_activities_count' => $course->group_activities_count,
        ];

        $progress_percent = $course->type_of_learning === 'BLENDED' ? Helper::calculateLearnerProgressPercentBlended((object)$progress) : Helper::calculateLearnerProgressPercentElearning((object)$progress);

        StudentCourseProgress::updateOrCreate([
            'user_id' => $user_id,
            'course_id' => $subtopic->course_id
        ], [
            ... $progress,
            'progress' => $progress_percent
        ]);

        return $this->sendResponse([], 'List of records.', 200);

    }
}
