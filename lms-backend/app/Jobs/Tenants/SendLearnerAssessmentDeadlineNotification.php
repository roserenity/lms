<?php

namespace App\Jobs\Tenants;

use App\Models\User;
use App\Models\Course;
use App\Models\StudentCompleteCourse;
use App\Models\StudentCourseProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Helpers\Helper;
use App\Helpers\StandaloneConfig;
use App\Jobs\Tenants\SendAssessmentDeadlineMail;
use App\Mail\AssessmentDeadline;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Enrollment;
use App\Notifications\AssessmentDeadlineNotification;
use Log;
use Mail;
use Illuminate\Support\Facades\Notification;

use Stancl\Tenancy\Concerns\HasATenantArgument;
use Carbon\Carbon;

class SendLearnerAssessmentDeadlineNotification implements ShouldQueue
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

        try {

            Log::info("Processing data for Tenant ID:". $this->tenantId);

            $today = now()->toDateString();
    
            $assessments = Assessment::query()
                ->select(['id', 'course_id', 'title', 'due_date'])
                ->whereNotNull('due_date')
                ->whereHas('course')
                ->with(['course' => fn($query) => $query->select(['id', 'uuid', 'title'])])->get();
    
            foreach ($assessments as $assessment) {
    
                $dueDate = json_decode($assessment->due_date, true); // Convert JSON string to array
    
                if (!isset($dueDate['end'])) {
                    Log::warning("Assessment ID {$assessment->id} has an invalid due_date format.");
                    continue;
                } else if(($dueDate['start'] == $dueDate['end'])) {
                    Log::warning("Assessment ID {$assessment->id} has the same start and end due date");
                    continue;
                }
    
                $endDate = Carbon::parse($dueDate['end']);
                $startDate = Carbon::parse($dueDate['start']);
                $daysDifference = $startDate->diffInDays($endDate);

                if ($daysDifference <= 3) {
                    // Notify them a day before the end date
                    $notifyDates = [$endDate->copy()->subDay()->toDateString()];
                } else {
                    // Standard notification: 7 days and 3 days before
                    $notifyDates = [
                        $endDate->copy()->subDays(7)->toDateString(),
                        $endDate->copy()->subDays(3)->toDateString(),
                    ];
                }

                Log::warning("Assessment ID {$assessment->id} notify date " . json_encode($notifyDates));
                Log::warning("Today's date: " . $today);
    
                if (!in_array($today, $notifyDates)) {
                    continue; // Skip if today is not a notify date
                }
    
                $enrollees = Enrollment::where('course_id', $assessment->course_id)->whereHas('user')->with('user')->get();
    
                foreach ($enrollees as $enrollee) {
    
                    if (isset($enrollee->user)) {
                        $assessment_score = AssessmentScore::where('user_id', $enrollee->user->id)->where('assessment_id', $assessment->id)->first();
        
                        if(!$assessment_score) {
                            $data = [
                                'name' => $enrollee->user->full_name,
                                'photo' => $enrollee->user->photo,
                                'course' => $assessment->course->title,
                                'course_uuid' => $assessment->course->uuid,
                                'title' => $assessment->title,
                                'deadline' => $dueDate['end'],
                                'text' => 'deadline is upcoming'
                            ];
            
                            Notification::send($enrollee->user, new AssessmentDeadlineNotification($data));

                            Log::info("Notification Sent To: " . json_encode($enrollee->user));

                            // $progress = StudentCourseProgress::where('course_id', $assessment->course->id)->where('user_id', $enrollee->user->id)->first();
                            
                            $url = StandaloneConfig::domainName() . "/course" . "/" .$assessment->course->uuid;

                            $apiKey = config("services.sender.key");

                            if($apiKey) { 
                                SendAssessmentDeadlineMail::dispatch([
                                    'name' => $enrollee->user->full_name,
                                    'course_title' => $assessment->course->title,
                                    'assessment_title' => $assessment->title,
                                    'assessment_due_date' => $dueDate['end'],
                                    'url' => $url,
                                    // 'current_progress' => $progress ? $progress->progress : 0,
                                    'email' => $enrollee->user->email
                                ]);

                                Log::info("Email Sent To: " . json_encode($enrollee->user));
                            } else {
                                
                                Mail::to($enrollee->user->email)->send(new AssessmentDeadline([
                                    'name' => $enrollee->user->full_name,
                                    'course_title' => $assessment->course->title,
                                    'assessment_title' => $assessment->title,
                                    'assessment_due_date' => $dueDate['end'],
                                    'url' => $url,
                                    'tenant_id' => StandaloneConfig::tenantId()
                                ]));
                            }

                            sleep(2);
                        }
                    }
                }
            }
    
            Log::info("Finished processing data");
        } catch (\Exception $e) {
            
            Log::error("Learner Assessment Deadline notification error: " . $e);
        }
    }
}
