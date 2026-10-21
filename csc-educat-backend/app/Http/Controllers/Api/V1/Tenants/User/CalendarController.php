<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenants\User\EventCourseResource;
use App\Http\Resources\Tenants\User\EventZoomMeetingResource;
use App\Http\Resources\Tenants\User\EventAssessmentResource;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ZoomMeeting;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function index(Request $request)
    {
        // $course_ids = $this->authUser()->enrollments->pluck('course_id');
        // $courses = EventResource::collection(Course::whereIn('id', $course_ids)->whereNotNull('available_until')->get());
        // return $this->sendResponse($courses, 'List of all events.', 200);

        $startOfMonth = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth();

        $enrolled_course_ids = Enrollment::enrolled()->get()->pluck('course_id');
        $courses = EventCourseResource::collection(
            Course::select(['id', 'uuid', 'title', 'available_until'])
                ->whereIn('id', $enrolled_course_ids)
                ->whereNotNull('available_until')
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('available_until->start_date', [$startOfMonth, $endOfMonth])
                          ->orWhereBetween('available_until->end_date', [$startOfMonth, $endOfMonth]);
                })
                ->get()
        );

        $enrolled_published_courses = $courses->pluck('id');

        $meetings = EventZoomMeetingResource::collection(
            ZoomMeeting::whereIn('course_id', $enrolled_course_ids)
                ->whereYear('data->start_date', $request->year)
                ->whereMonth('data->start_date', $request->month)
                ->get()
        );
        
        $assessments = EventAssessmentResource::collection(
            Assessment::with([
                    'course'
                ])->whereIn('course_id', $enrolled_course_ids)
                ->whereNotNull('due_date')
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('due_date->start', [$startOfMonth, $endOfMonth])
                          ->orWhereBetween('due_date->end', [$startOfMonth, $endOfMonth]);
                })         
                ->get()
        );

        $events = [];

        if(in_array("course", $request->type) || empty($request->type)){
            $events = [...$events, ...$courses];
        }

        if(in_array("meeting", $request->type) || empty($request->type)){
            $events = [...$events, ...$meetings];
        }

        if(in_array("assessment", $request->type) || empty($request->type)){
            $events = [...$events, ...$assessments];
        }

        return $this->sendResponse($events, 'List of all events.', 200);
    }
}
