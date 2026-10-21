<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;


use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Exports\Tenants\Teacher\ReportingCoursesSummary;
use App\Exports\Tenants\Teacher\ReportingCourseEnrolleesSummary;
use App\Exports\Tenants\Teacher\ReportingCourseCompletersSummary;
use App\Exports\Tenants\Teacher\ReportingAssessmentsSummary;

use App\Http\Controllers\Controller;
use App\Jobs\Tenants\ProcessAdminReportingCoursesExport;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exports;
use App\Models\Invoice;
use App\Models\User;
use App\Models\StudentCompleteCourse;
use App\Models\StudentModuleProgress;
use App\Models\ZoomMeetingAttendee;

use Log;

use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function showTotalListCards()
    {
        if(request('course_id')) {
            $course = Course::where('id', request('course_id'))->withCount(['invoices'])->first();

            $total_enrolled_users_count = Enrollment::where('course_id', request('course_id'))->distinct('user_id')->count();
            $total_transactions_count = Invoice::where('course_id', request('course_id'))->distinct('user_id')->count();
            $completers_count = StudentCompleteCourse::where('course_id', request('course_id'))->distinct('user_id')->count();
            $certificate_count = Certificate::where('course_id', request('course_id'))->distinct('user_id')->count();
            
            return $this->sendResponse(
                [
                    'total_enrolled_users_count' => $total_enrolled_users_count,
                    'total_transactions_count' => $total_transactions_count,
                    'total_completers_count' => $completers_count,
                    'total_certificate_count' => $certificate_count
                ],
                'Total list cards instructor reporting',
                200
            );

        } else {
            // $courses = Course::where('status', '!=', 'DRAFTED')
            //     ->where('uploader_id', $this->authUser()->id)
            //     ->orWhereRelation('shared_to_users', 'user_id', $this->authUser()->id)
            //     ->get()->pluck('id');

            $courses = Course::pluck('id');
            $total_enrollees_count = Enrollment::whereIn('course_id', $courses)->count();
            $total_enrollees_unique_count = Enrollment::whereIn('course_id', $courses)->distinct('user_id')->count();


            // $completers_count = StudentCompleteCourse::whereIn('course_id', $courses)
            //     ->whereHas('user', function($query) use ($courses) {
            //         $query->whereHas('enrollments', function($subquery) use ($courses) {
            //             $subquery->whereIn('course_id', $courses);
            //         });
            //     })
            //     ->whereHas('user')
            //     ->count();

            $completers_count = StudentCompleteCourse::whereIn('student_complete_courses.course_id', $courses)
                ->whereHas('user')
                ->join('enrollments', function ($join) {
                    $join->on('student_complete_courses.user_id', '=', 'enrollments.user_id')
                        ->on('student_complete_courses.course_id', '=', 'enrollments.course_id')
                        ->whereNull('enrollments.deleted_at');
                })
                ->whereNull('student_complete_courses.deleted_at')
                ->count();

            $completers_unique_count = StudentCompleteCourse::whereIn('student_complete_courses.course_id', $courses)
                ->whereHas('user')
                ->join('enrollments', function ($join) {
                    $join->on('student_complete_courses.user_id', '=', 'enrollments.user_id')
                        ->on('student_complete_courses.course_id', '=', 'enrollments.course_id')
                        ->whereNull('enrollments.deleted_at');
                })
                ->whereNull('student_complete_courses.deleted_at')
                ->distinct('student_complete_courses.user_id')
                ->count();

            $certificates_count = Certificate::whereIn('course_id', $courses)
                ->get()
                ->count();
            
            return $this->sendResponse(
                [
                    'total_enrollees_count' => $total_enrollees_count,
                    'total_enrollees_unique_count' => $total_enrollees_unique_count,
                    'courses_count' => $courses->count(),
                    'completers_count' => $completers_count,
                    'completers_unique_count' => $completers_unique_count,
                    'certificates_count' => $certificates_count 
                ],
                'Total list cards instructor reporting',
                200
            );
        }
    }

    
    public function showCoursesEnrolleeCount(Request $request)
    {
        $courses = Course::select(['id', 'title', 'uploader_id', 'status', 'available_until', 'type_of_learning'])
            ->where('status', '!=', COURSE::IS_DRAFT)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('uploader_id', $this->authUser()->id)
                    ->orWhereRelation('shared_to_users', 'user_id', $this->authUser()->id);
                });
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->search . '%');
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
            ->withCount(['enrollments' => function($query) use ($request) {
                $query->whereHas('user')
                    ->when(($request->enrolled_from || $request->enrolled_to), function ($query) use ($request){
                        FilterHelper::queryCreatedAt($query, $request->enrolled_from, $request->enrolled_to);
                    });
            }])
            ->get();
        
        return $this->sendResponse($courses, 'List of all reporting enrollee count', 200);
    }

    public function showAssessments(Request $request)
    {
        $assessments = Assessment::select(['id', 'title', 'course_id', 'created_at', 'type_of_assessment'])
            ->when($request->course_id, function($query) use ($request) {
                $query->where('course_id', $request->course_id);
            })
            ->whereHas('course', function($query){
                $query->where('status', '!=', Course::IS_DRAFT);
            })
            ->with(['course' => function($query) {
                $query->select(['id', 'title']);
            }])
            ->get();
        
        return $this->sendResponse($assessments, 'List of all assessments', 200);
    }

    public function showCourses(Request $request)
    {
        if($request->paginate) {
            $courses = Course::select(['id', 'title', 'uploader_id', 'status', 'available_until', 'type_of_learning', 'training_hours' ,'course_code', 'created_at'])
                ->where('status', '!=', COURSE::IS_DRAFT)
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('uploader_id', $this->authUser()->id)
                          ->orWhereRelation('shared_to_users', 'user_id', $this->authUser()->id);
                      });
                })
                ->when($request->search, function ($query) use ($request) {
                    FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
                })
                ->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                    FilterHelper::queryAvailabilityDates($query, $request->date_from, $request->date_to);
                })
                ->when($request->status, function ($query) use ($request) {
                    $query->whereIn('status', $request->status ? [$request->status] : [ Course::IS_PUBLISH, COURSE::IS_UNPUBLISH ]);
                })
                ->when($request->type_of_learning, function ($query) use ($request) {
                    $query->where('type_of_learning', $request->type_of_learning);
                })
                ->withCount(['enrollments' => function($query) use ($request) {
                        $query->whereHas('user', function($query) use ($request){
                            $query->whereHas('enrollments', function($subQuery) use ($request) {
                                $subQuery->when(($request->enrolled_from || $request->enrolled_to), function ($query) use ($request){
                                    FilterHelper::queryCreatedAt($query, $request->enrolled_from, $request->enrolled_to);
                                });
                            });
                        });
                    }, 
                    'enrollments as total_enrollments' => function($enrollments){
                        $enrollments->has('user');
                    },
                    'assessments'
                ])
                ->paginate($request->paginate);

            $courses->getCollection()->each(function($course) use ($request){
                $course->completed_course_count = StudentCompleteCourse::where('course_id', $course->id)
                    ->has('user')
                    ->whereHas('user.enrollments', function($query) use ($course){
                        $query->where('course_id', $course->id);
                    })
                    ->distinct('user_id')
                    ->get()
                    ->count();
            });

        } else {
            $courses = Course::where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('uploader_id', $this->authUser()->id)
                        ->orWhereRelation('shared_to_users', 'user_id', $this->authUser()->id);
                    });
                })
                ->where('status', '!=', COURSE::IS_DRAFT)
                ->withCount(['enrollments' => function($query) use ($request) {
                        $query->whereHas('user')
                        ->when(( $request->date_from || $request->date_to), function ($query) use ($request) {
                            FilterHelper::queryCreatedAt($query,  $request->date_from,$request->date_to);
                        });
                    },
                ])
                ->when(!empty(request('search')), function ($query) {
                    $query->where('title', 'LIKE', '%' . request('search') . '%');
                })
                ->get()
                ->makeHidden([
                    'course_card',
                    'image',
                    'total_done_assessments',
                    'total_done_course_evaluations',
                    'total_done_module_evaluations',
                    'total_done_group_activities',
                    'total_done_sub_topics',
                    'total_done_topics'
                ]);
        }

        return $this->sendResponse($courses, 'List of all reporting courses.', 200);
    }

    public function showCoursesExport(Request $request){
        

        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'course',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessAdminReportingCoursesExport::dispatch(
            $export->id,
            $request->search,
            $this->authUser()->id,
            $request->date_from,
            $request->date_to,
            $request->status,
            $request->type_of_learning, 
            $request->enrolled_from, 
            $request->enrolled_to
        );
    
        return response()->json([
            'message' => 'Export has been queued.',
            'status' => 'processing',
        ]);

    }

    public function showCourseCompleters(Request $request, $course_id)
    {
        $searchParts = explode(' ', request('search'));

        $users =  StudentCompleteCourse::where('course_id', $course_id)
            ->whereHas('user', function($query) use ($request){
                $query->whereHas('enrollments', function($subQuery) use ($request) {
                    $subQuery->when(($request->date_from || $request->date_to), function ($query) use ($request){
                        FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
                    });
                });
            })
            ->with('user', function($user) use ($course_id) {
                $user->with('enrollments', function ($query) use ($course_id) {
                    $query->where('course_id', $course_id);
                });
            })
            ->when(!empty($request->search), function ($query) use ($searchParts) {
                $query->whereHas('user', function ($query) use ($searchParts) {
                    Helper::queryNameEmailSearch($searchParts, $query);
                });
            })
            ->distinct('user_id')
            ->paginate($request->paginate);
    
        return $this->sendResponse($users, 'List of user completers', 200);
    }

    public function showCourseCompletersExport(Request $request, $course_id)
    {
        return new ReportingCourseCompletersSummary($request->search, $course_id, $request->date_from, $request->date_to);
    }

    public function showCourseUsers(Request $request, $course_id)
    {

        $searchParts = explode(' ', $request->search);

        $course = Course::where('id', $course_id)->first();

        if (!$course) {
            return $this->sendResponse([], 'No course found.', 404);
        }

        $enrollments = Enrollment::where('course_id', $course->id)
            ->whereHas('user', function($query) use ($request, $searchParts, $course) {
                $query->when($request->search, function ($query) use ($request, $searchParts) {
                    Helper::queryNameSearch($searchParts, $query);
                })
                ->when($request->progress_status, function ($query) use ($request, $course) {
                    if ($request->progress_status == 1) {
                        return $query->whereHas('certificates', fn($certificates) 
                            => $certificates->where('course_id', $course->id));
                    } else {
                        return $query->whereDoesntHave('certificates', fn($certificates) 
                        => $certificates->where('course_id', $course->id));
                    }
                })->whereHas('enrollments', function($subQuery) use ($request) {
                    $subQuery->when(($request->date_from || $request->date_to), function ($query) use ($request){
                        FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
                    });
                });
            })
            ->with([
                'course' => function ($query) use ($request) {
                    $query->select(['id', 'title', 'type_of_learning', 'uuid'])
                        ->withCount(['assessments', 'topics', 'sub_topics', 'course_evaluations', 'module_evaluations', 'zoom_meetings', 'group_activities'])
                        ->withCount([
                            'my_assessment_score as total_checked_assessments' => function ($query) {
                                $query->where('checked', true);
                            }
                        ]);
                    },
                'user' => function($query) use ($course) {
                        $query->select(['id', 'first_name', 'last_name', 'data', 'email', 'last_name', 'suffix'])
                            ->with(['completed_course' => fn($completed_course) 
                                => $completed_course->where('course_id', $course->id),
                            'certificates' => fn($certificates) 
                                => $certificates->where('course_id', $course->id)->with('image')->whereHas('image')]);
                    }
            ])
            ->orderBy('created_at', 'desc')
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
            $course = Helper::setLearnerCourseData($course, $enrollment->user_id);
            $course['total_checked_assessments'] = $course->total_checked_assessments ?? 0;

            // Get the latest content done
            $latestContentDone = StudentModuleProgress::where('course_id', $course->id)
                ->where('user_id', $enrollment->user_id)
                ->done()
                ->latest()
                ->first();

            // Get the latest zoom attended
            $latestZoomAttended = ZoomMeetingAttendee::whereIn('zoom_meeting_id', $course->zoom_meetings()->pluck('id'))
                ->where('present', 1)
                ->where('user_id', $enrollment->user_id)
                ->latest()
                ->first();

            // Parse the creation dates with Carbon
            $latestContentDoneDate = $latestContentDone ? Carbon::parse($latestContentDone->created_at) : null;
            $latestZoomAttendedDate = $latestZoomAttended ? Carbon::parse($latestZoomAttended->created_at) : null;

            // Find the latest activity date
            $latestActivityDate = $latestContentDoneDate && $latestZoomAttendedDate 
            ? $latestContentDoneDate->max($latestZoomAttendedDate) 
            : ($latestContentDoneDate ?: $latestZoomAttendedDate);

            $course['latest_activity_date'] = $latestActivityDate;

            $enrollment['progress'] = $course->type_of_learning === 'BLENDED' ? Helper::calculateLearnerProgressPercentBlended($course) : Helper::calculateLearnerProgressPercentElearning($course);

        }
        return $this->sendResponse($enrollments, 'List of all enrollees.', 200);
    }

    public function showCourseUsersExport(Request $request, $course_id)
    {
        return new ReportingCourseEnrolleesSummary($request->search, $course_id, $request->date_from, $request->date_to);
    }

    public function showTotalUsersPerCourse(Request $request)
    {
        $data = [];

        if ($request->filter === 'All') {
            $course = Course::withCount('enrollments')
                ->where('status', Course::IS_PUBLISH)
                ->where('id', $request->course_id)
                ->first();

            $data[$course->title] = $course->enrollments_count;
        } else {
            $custom_field = CustomRegistrationField::where('input_field_label', $request->filter)->firstOrFail();
            $custom_page = CustomRegistrationPage::where('id', $custom_field->custom_registration_page_id)->first();
            foreach ($custom_field->data as $key => $value) {
                $course = Course::with('enrollments')
                ->where('status', Course::IS_PUBLISH)
                ->where('id', $request->course_id)
                ->first();

                $user_ids = $course->enrollments->pluck('user_id');

                $data[$value] = User::whereIn('id', $user_ids)->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $value)->count();
            }
        }

        return $this->sendResponse($data, 'Total enrolled per course instructor reporting.', 200);
    }

    public function listOfCustomRegistrationFields()
    {
        $custom_fields = CustomRegistrationField::where('input_field_type', 'dropdown')->get()->pluck(['input_field_label']);

        return $this->sendResponse($custom_fields, 'List of custom registration fields.', 200);
    }

    public function showAssessmentData($course_id)
    {
        if(request('paginate'))
        {
            $assessments = Assessment::select(['id', 'title', 'course_id'])
                ->where('course_id', $course_id)
                ->when(!empty(request('search')), function ($query) {
                    $query->where('title', 'LIKE', '%' . request('search') . '%');
                })
                ->withCount([
                    'assessment_scores as passed_count' => function($query){
                        $query->where('status', AssessmentScore::IS_PASSED)->where('active', 1);
                    },
                    'assessment_scores as failed_count' => function($query){
                        $query->where('status', AssessmentScore::IS_FAILED)->where('active', 1);
                    },
                    'assessment_scores as answered_count' => function($query){
                        $query->where('active', 1);
                    },
                ])
                ->paginate(request('paginate'));
        } else {
            $assessments = Assessment::select(['id', 'title', 'course_id'])
                ->where('course_id', $course_id)
                ->withCount([
                    'assessment_scores as passed_count' => function($query){
                        $query->where('status', AssessmentScore::IS_PASSED)->where('active', 1);
                    },
                    'assessment_scores as failed_count' => function($query){
                        $query->where('status', AssessmentScore::IS_FAILED)->where('active', 1);
                    },
                    'assessment_scores as answered_count' => function($query){
                        $query->where('active', 1);
                    },
                ])
                ->get();
        }

        return $this->sendResponse($assessments, 'List of assessments for reporting', 200);
    }

    public function showAssessmentDataExport($course_id, Request $request)
    {
        return new ReportingAssessmentsSummary($request->search, $course_id);
    }
}
