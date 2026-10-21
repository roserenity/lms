<?php

namespace App\Jobs\Tenants;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

use App\Models\StudentCompleteCourse;
use App\Models\User;

class SetLearnerCourseCompletionActivityLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Processing data for Tenant ID:". $this->tenantId);
        
        $completers = StudentCompleteCourse::all();

        foreach($completers as $completer)
        {
            $user = User::where('id', $completer->user_id)->first();

            if($user) {
    
                $activity = Activity::where('log_name', 'user_course_complete')
                    ->where('causer_id', $user->id)
                    ->where('properties->course_id', $completer->course_id)
                    ->latest()
                    ->first();

                if ($activity) {
                    $activity->update([
                        'created_at' => $completer->created_at,
                        'updated_at' => $completer->created_at,
                    ]);
                } else {
                    activity('user_course_complete')
                        ->causedBy($user)
                        ->withProperties([
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'course_id' => $completer->course_id,
                        ])
                        ->event('created')
                        ->performedOn($user)
                        ->log('User Course Completed');

                    Activity::where('log_name', 'user_course_complete')
                        ->where('causer_id', $user->id)
                        ->where('properties->course_id', $completer->course_id)
                        ->latest()
                        ->first()
                        ->update([
                            'created_at' => $completer->created_at,
                            'updated_at' => $completer->created_at,
                        ]);
                }
            }
        }
        
        Log::info("Finished processing data");
    }
}
