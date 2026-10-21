<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use App\Exports\Tenants\Admin\ActivityLogsExport;
use App\Jobs\Tenants\ProcessAdminActivityLogExport;
use App\Models\Advisory;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

use App\Models\Course;
use App\Models\Exports;
use App\Models\User;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function activityLogStats()
    {
        $courses = Course::pluck('id');

        $user_course_complete_count = Activity::whereIn('properties->course_id', $courses)
            ->where('log_name', 'user_course_complete')
            ->count();
        
        $user_certificate_gen_count = Activity::whereIn('properties->course_id', $courses)
            ->where('log_name', 'user_certificate_gen')
            ->count();
            
        return $this->sendResponse([
            'user_course_complete_count' => $user_course_complete_count,
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
        
        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'activity_log',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessAdminActivityLogExport::dispatch(
            $export->id,
            $request->log_name,
        );
    
        return response()->json([
            'message' => 'Export has been queued',
            'status' => 'processing',
        ]);

    }
}
