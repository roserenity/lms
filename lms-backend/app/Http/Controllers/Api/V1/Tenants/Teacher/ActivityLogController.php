<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use App\Exports\Tenants\Teacher\ActivityLogsExport;
use App\Models\Advisory;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

use App\Models\Course;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function activityLogStats()
    {
        $courses = Course::pluck('id');
        $users = User::pluck('id');

        $user_course_complete_count = 0;

        foreach (array_chunk($courses->toArray(), 1000) as $courseChunk) {
            foreach (array_chunk($users->toArray(), 1000) as $userChunk) {
                $user_course_complete_count += Activity::whereIn('properties->course_id', $courseChunk)
                    ->where('log_name', 'user_course_complete')
                    ->whereIn('properties->user_id', $userChunk)
                    ->join('enrollments', function ($join) {
                        $join->on('properties->user_id', '=', 'enrollments.user_id')
                            ->on('properties->course_id', '=', 'enrollments.course_id')
                            ->whereNull('deleted_at');
                    })
                    ->count();
            }
        }

        $user_course_complete_unique_count = 0;

        foreach (array_chunk($courses->toArray(), 1000) as $courseChunk) {
            foreach (array_chunk($users->toArray(), 1000) as $userChunk) {
                $user_course_complete_unique_count += Activity::whereIn('properties->course_id', $courseChunk)
                    ->where('log_name', 'user_course_complete')
                    ->whereIn('properties->user_id', $userChunk)
                    ->join('enrollments', function ($join) {
                        $join->on('properties->user_id', '=', 'enrollments.user_id')
                            ->on('properties->course_id', '=', 'enrollments.course_id')
                            ->whereNull('deleted_at');
                    })
                    ->distinct('user_id')
                    ->count();
            }
        }
        
        $user_certificate_gen_count = Activity::whereIn('properties->course_id', $courses)
            ->where('log_name', 'user_certificate_gen')
            ->get()
            ->count();
            
        return $this->sendResponse([
            'user_course_complete_count' => $user_course_complete_count,
            'user_course_complete_unique_count' => $user_course_complete_unique_count,
            'user_certificate_gen_count' => $user_certificate_gen_count,
        ], 'List of activity log stats.', 200);
    }

    public function index(Request $request)
    {
        $courses = Course::pluck('id');

        $activity = Activity::latest()
            ->where(function ($query) use ($courses) {
                $query->whereIn('properties->course_id', $courses) // Matches specific course IDs
                    ->orWhereNull('properties->course_id');      // Includes those without course_id
            })
            ->when(!empty($request->log_name), function ($query) use ($request) {
                $query->whereIn('log_name', $request->log_name);
            })
            ->paginate($request->pagination)
            ->through(function ($log) {
                $courseId = data_get($log, 'properties.course_id');
                $advisoryId = data_get($log, 'properties.advisory_id');
                $target_id  = data_get($log, 'properties.user_id');
                $causerId = data_get($log, 'causer_id');

                $log->course = $courseId ? Course::withTrashed()->select(['id', 'title'])->find($courseId)?->toArray() : null;
                $log->advisory = $advisoryId ? Advisory::withTrashed()->with(['image'])->find($advisoryId)?->toArray() : null;
                $log->target = $target_id ? User::withTrashed()->select(['id', 'first_name', 'middle_name', 'last_name', 'email'])->find($target_id)?->toArray() : null;
                $log->causer = $causerId ? User::withTrashed()->select(['id', 'first_name', 'middle_name', 'last_name', 'email'])->find($causerId)?->toArray() : null;
                return $log;
            });

        return $this->sendResponse($activity, 'List of all activity logs.', 200);
    }
    
    public function activityLogExport(Request $request)
    {
        return new ActivityLogsExport($request->log_name);
    }
}
