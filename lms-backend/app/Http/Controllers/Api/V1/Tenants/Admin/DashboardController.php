<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Ticket;
use App\Models\Invoice;
use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Jobs\Tenants\ProcessAdminCourseExport;
use App\Jobs\Tenants\ProcessAdminCourseUsersExport;
use App\Jobs\Tenants\ProcessAdminCourseCompletersExport;
use App\Models\Exports;
use App\Models\StudentCompleteCourse;

use Illuminate\Support\Carbon;

use Log;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function showTotalListCards(Request $request)
    {

        $courses = Course::pluck('id');

        $total_registered_users = User::when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<=', $request->date_to);
            })->count();
            
        $total_enrolled_users = Enrollment::whereIn('course_id', $courses)->when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<=', $request->date_to);
            })->count();
        
        $total_unique_enrolled_users = Enrollment::whereIn('course_id', $courses)->when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<=', $request->date_to);
            })->distinct('user_id')->count();
        
        $total_completers_count = StudentCompleteCourse::whereIn('student_complete_courses.course_id', $courses)
            ->whereHas('user')
            ->join('enrollments', function ($join) {
                $join->on('student_complete_courses.user_id', '=', 'enrollments.user_id')
                    ->on('student_complete_courses.course_id', '=', 'enrollments.course_id')
                    ->whereNull('enrollments.deleted_at');
            })
            ->when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereDate('student_complete_courses.created_at', '>=', $request->date_from)->whereDate('student_complete_courses.created_at', '<=', $request->date_to);
            })
            ->whereNull('student_complete_courses.deleted_at')
            ->count();

        $total_unique_completers_count = StudentCompleteCourse::whereIn('student_complete_courses.course_id', $courses)
            ->whereHas('user')
            ->join('enrollments', function ($join) {
                $join->on('student_complete_courses.user_id', '=', 'enrollments.user_id')
                    ->on('student_complete_courses.course_id', '=', 'enrollments.course_id')
                    ->whereNull('enrollments.deleted_at');
            })
            ->when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereDate('student_complete_courses.created_at', '>=', $request->date_from)->whereDate('student_complete_courses.created_at', '<=', $request->date_to);
            })
            ->whereNull('student_complete_courses.deleted_at')
            ->distinct('student_complete_courses.user_id')
            ->count();

        $total_onboarded_instructors = User::where('role', User::ROLE_INSTRUCTOR)
            ->when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<=', $request->date_to);
            })->count();

        $total_transactions = Invoice::when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<=', $request->date_to);
            })->count();

        $total_courses = Course::when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<=', $request->date_to);
            })->count();
            
        $total_tickets = Ticket::when($request->date_from && $request->date_to, function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<=', $request->date_to);
            })->count();

        $total_certificates_count = Certificate::whereHas('user')->whereHas('course')->count();
        $total_unique_certificates_count = Certificate::whereHas('user')->whereHas('course')->count();

        return $this->sendResponse(
            [
                'total_registered_users' => $total_registered_users,
                'total_enrolled_users' => $total_enrolled_users,
                'total_unique_enrolled_users' => $total_unique_enrolled_users,
                'total_completers_count' => $total_completers_count,
                'total_unique_completers_count' => $total_unique_completers_count,
                'total_onboarded_instructors' => $total_onboarded_instructors,
                'total_transactions' => $total_transactions,
                'total_courses' => $total_courses,
                'total_tickets' => $total_tickets,
                'total_certificates_count' => $total_certificates_count,
                'total_unique_certificates_count' => $total_unique_certificates_count
            ],
            'Total list cards.',
            200
        );
    }

    public function showTotalPieGraphs(Request $request)
    {
        $datesCondition = ($request->date_from && $request->date_to)
            ? [['created_at', '>=', $request->date_from], ['created_at', '<=', $request->date_to]]
            : [];

        $total_learner_count = User::user()->where($datesCondition)->count();
        $total_teacher_count = User::teacher()->where($datesCondition)->count();
        $total_admin_count = User::admin()->where($datesCondition)->count();
        $total_open_tickets_count = Ticket::open()->where($datesCondition)->count();
        $total_pending_tickets_count = Ticket::pending()->where($datesCondition)->count();
        $total_on_hold_tickets_count = Ticket::onhold()->where($datesCondition)->count();
        $total_solved_tickets_count = Ticket::solved()->where($datesCondition)->count();
        $total_closed_tickets_count = Ticket::closed()->where($datesCondition)->count();
        $total_pending_transactions_count = Invoice::pending()->where($datesCondition)->count();
        $total_success_transactions_count = Invoice::success()->where($datesCondition)->count();
        $total_failed_transactions_count = Invoice::failed()->where($datesCondition)->count();
        $total_cancelled_transactions_count = Invoice::cancelled()->where($datesCondition)->count();

        return $this->sendResponse(
            [
                'Registered Users' => [
                    'series' => [$total_learner_count, $total_teacher_count, $total_admin_count],
                    'labels' => ['Learner', 'Instructor', 'Admin']
                ],

                'Tickets Received' => [
                    'series' => [$total_open_tickets_count, $total_pending_tickets_count, $total_on_hold_tickets_count, $total_solved_tickets_count, $total_closed_tickets_count],
                    'labels' => ['Open', 'Pending', 'On Hold', 'Solved', 'Closed'],
                ],

                'Transactions' => [
                    'series' => [$total_pending_transactions_count, $total_success_transactions_count, $total_failed_transactions_count, $total_cancelled_transactions_count],
                    'labels' => ['Pending', 'Success', 'Failed', 'Cancelled'],
                ]
            ],
            'Totals for graphs.',
            200
        );
    }

    public function showCourses(Request $request)
    {
        $data = [];
        if ($request->filter === 'Total Users per Course') {
            $data = Course::select(['id', 'status', 'uploader_id', 'title', 'status', 'type_of_learning', 'created_at', 'available_until', 'course_code'])
                ->when($request->search, function ($query) use ($request) {
                    FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
                })
                ->when($request->status, function ($query) use ($request) {
                    $query->whereIn('status', $request->status ? [$request->status] : [ Course::IS_PUBLISH, COURSE::IS_UNPUBLISH ]);
                })
                ->when($request->type_of_learning, function ($query) use ($request) {
                    $query->where('type_of_learning', $request->type_of_learning);
                })
                ->with(['uploader' => fn($uploader) => $uploader->select(['id', 'first_name', 'last_name', 'suffix'])])
                ->withCount([
                    'enrollments' => function($enrollments) use ($request) {
                        $enrollments->has('user')
                        ->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                            FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
                        })->distinct('user_id');
                    },
                    'enrollments as total_enrollments' => function($enrollments){
                        $enrollments->has('user');
                    },
                ])
                ->when(($request->created_from || $request->created_to), function ($query) use ($request) {
                    FilterHelper::queryAvailabilityDates($query, $request->created_from, $request->created_to);
                })
                ->latest()
                ->paginate($request->paginate);

            $data->getCollection()->each(function($course) use ($request){
                $course->completed_course_count = StudentCompleteCourse::where('course_id', $course->id)
                    ->whereHas('user', function ($query) use ($course) {
                        $query->whereHas('enrollments', function ($q) use ($course) {
                            $q->where('course_id', $course->id);
                        });
                    })
                    ->distinct('user_id')
                    ->count('user_id');
            });
        } else {
            $_data = [];
            $custom_field = CustomRegistrationField::where('input_field_label', $request->filter)->firstOrFail();
            $custom_page = CustomRegistrationPage::where('id', $custom_field->custom_registration_page_id)->first();
            foreach ($custom_field->data as $value) {
                if(stristr($value, $request->search)) {
                    array_push($_data, [
                        'title' => $value,
                        'users_count' => User::user()->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $value)
                            ->whereHas('enrollments', function($query) use ($request) {
                                $query->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                                    FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
                                });
                            })
                            ->count(),
                        'course_complete_count' => User::user()->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $value)
                            ->whereHas('completed_course', function($query) use ($request) {
                                $query->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                                    FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
                                });
                            })
                            ->count(),
                        'total_enrollments' => User::user()->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $value)
                            ->whereHas('enrollments')->count()
                    ]);
                }
            }

            $perPage = request('paginate', 10);
            $page = request('page', 1);

            $data = new LengthAwarePaginator(
                collect($_data)->forPage($page, $perPage),
                collect($_data)->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );


        }

        return $this->sendResponse($data, 'List of all admin dashboard courses', 200);
    }
    

    public function showCourseUsers(Request $request)
    {
        $users = [];

        $searchParts = explode(' ', request('search'));

        if ($request->filter === 'Total Users per Course') {
            
            $users = User::select(['id', 'role', 'first_name', 'last_name', 'middle_name', 'suffix', 'email', 'status'])
            ->user()
            ->whereHas('enrollments', function ($query) use ($request) {
                $query->where('course_id', $request->value)
                    ->when($request->date_from && $request->date_to, function ($query) use ($request) {
                        $query->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<=', $request->date_to);
                    });
            })
            ->with('enrollments', function ($query) use ($request) {
                $query->where('course_id', $request->value)
                    ->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                        FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
                    });
            })
            ->when(!empty(request('search')), function ($query) use ($searchParts) {
                Helper::queryNameEmailSearch($searchParts, $query);
            })
            ->paginate($request->paginate);
        } else {
            $custom_field = CustomRegistrationField::where('input_field_label', $request->filter)->firstOrFail();
            $custom_page = CustomRegistrationPage::where('id', $custom_field->custom_registration_page_id)->first();
            $users = User::user()->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $request->listName)
                ->when(!empty(request('search')), function ($query) use ($searchParts) {
                    Helper::queryNameEmailSearch($searchParts, $query);
                })
                ->whereHas('enrollments', function($enrollments) use ($request) {
                    $enrollments->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                        FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
                    });
                })
                ->paginate($request->paginate);
        }

        return $this->sendResponse($users, 'List of all users.', 200);
    }

    public function showTotalUsersPerCourse(Request $request)
    {
        $enrolled_from = $request->enrolled_from ? Carbon::parse($request->enrolled_from)->format('y-m-d') : '';
        $enrolled_to = $request->enrolled_to ? Carbon::parse($request->enrolled_to)->format('y-m-d') : '';

        if ($request->filter === 'Total Users per Course') {
            $data = Course::select(['id', 'title', 'uploader_id', 'status', 'available_until', 'type_of_learning'])
                ->where('status', '!=', COURSE::IS_DRAFT)
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
                ->withCount(['enrollments' => function($query) use ($enrolled_from, $enrolled_to) {
                    $query->whereHas('user')
                        ->when(($enrolled_from || $enrolled_to), function ($query) use ($enrolled_from, $enrolled_to){
                            FilterHelper::queryCreatedAt($query, $enrolled_from, $enrolled_to);
                        });
                }])
                ->get();
        } else {
            $custom_field = CustomRegistrationField::where('input_field_label', $request->filter)->firstOrFail();
            $custom_page = CustomRegistrationPage::where('id', $custom_field->custom_registration_page_id)->first();
            foreach ($custom_field->data as $key => $value) {
                $data[$value] = User::user()->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $value)
                    ->whereHas('enrollments', function($enrollments) use ($request, $enrolled_from, $enrolled_to) {
                        $enrollments->when(($enrolled_from || $enrolled_to), function ($query) use ($enrolled_from, $enrolled_to){
                            FilterHelper::queryCreatedAt($query, $enrolled_from, $enrolled_to);
                        });
                    })->count();
            }
        }
        
        return $this->sendResponse($data, 'List of all enrollee count', 200);
    }

    public function showCourseCompleters(Request $request)
    {
        $data = [];

        $searchParts = explode(' ', request('search'));

        if ($request->filter === 'Total Users per Course') {
            $data =  StudentCompleteCourse::where('course_id', $request->value)
                ->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                    FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
                })
                ->has('user')
                ->whereHas('user.enrollments', function($query) use ($request) {
                    $query->where('course_id', $request->value);
                })
                ->with('user', function($user) use ($request, $searchParts) {
                    $user->with('enrollments', function ($query) use ($request, $searchParts) {
                        $query->where('course_id', $request->value);
                    });
                })
                ->when(!empty($request->search), function ($query) use ($request, $searchParts) {
                    $query->whereHas('user', function ($query) use ($searchParts) {
                        Helper::queryNameEmailSearch($searchParts, $query);
                    });
                })
                ->distinct('user_id')
                ->paginate($request->paginate);
        } else {
            $custom_field = CustomRegistrationField::where('input_field_label', $request->filter)->firstOrFail();
            $custom_page = CustomRegistrationPage::where('id', $custom_field->custom_registration_page_id)->first();
            $data = User::user()->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $request->listName)
                ->when(!empty(request('search')), function ($query) use ($searchParts) {
                    Helper::queryNameEmailSearch($searchParts, $query);
                })
                ->whereHas('completed_course', function($completed_course) use ($request) {
                    $completed_course->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                        FilterHelper::queryCreatedAt($query,  $request->date_from,  $request->date_to);
                    });
                })
                ->paginate($request->paginate);
        }
        return $this->sendResponse($data, 'Total Completions', 200);
    }

    public function listOfCustomRegistrationFields()
    {
        $custom_fields = CustomRegistrationField::where('input_field_type', 'dropdown')->get()->pluck(['input_field_label']);

        return $this->sendResponse($custom_fields, 'List of custom registration fields.', 200);
    }

    public function listOfCustomRegistrationFieldsWithOptions()
    {
        $custom_fields = CustomRegistrationField::where('input_field_type', 'dropdown')->select(['input_field_name', 'data', 'input_field_label'])->get();

        return $this->sendResponse($custom_fields, 'List of custom registration fields with options', 200);
    }

    public function dashboardCoursesExport(Request $request)
    {
        
        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'course',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessAdminCourseExport::dispatch(
            $export->id,
            $request->filter,
            $request->search,
            $request->date_from,
            $request->date_to,
            $request->status,
            $request->type_of_learning,
            $request->created_from,
            $request->created_to
        );
    
        return response()->json([
            'message' => 'Export has been queued',
            'status' => 'processing',
        ]);

    }

    public function dashboardCourseUsersExport(Request $request)
    {

        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'course',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessAdminCourseUsersExport::dispatch(
            $export->id,
            $request->filter,
            $request->value, 
            $request->field, 
            $request->search, 
            $request->date_from, 
            $request->date_to
        );
    
        return response()->json([
            'message' => 'Export has been queued',
            'status' => 'processing',
        ]);

    }

    public function dashboardCourseCompletersExport(Request $request)
    {
        
        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'course',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessAdminCourseCompletersExport::dispatch(
            $export->id,
            $request->filter,
            $request->value, 
            $request->field, 
            $request->search, 
            $request->date_from, 
            $request->date_to
        );
    
        return response()->json([
            'message' => 'Export has been queued',
            'status' => 'processing',
        ]);

    }
}
