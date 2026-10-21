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
use App\Models\Certificate;

use Log;
use Stancl\Tenancy\Concerns\HasATenantArgument;

class SaveUserProgress implements ShouldQueue
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

        $learners = User::query()->select('id')->with('enrollments')->get();

        foreach ($learners as $learner) {
            foreach($learner->enrollments as $enrollment) {
                $course = Course::query()->select(['id', 'type_of_learning'])->where('id', $enrollment->course_id)
                    ->withCount(['assessments', 'topics', 'sub_topics', 'course_evaluations', 'module_evaluations', 'zoom_meetings', 'group_activities'])
                    ->first();
                
                if($course) {

                    $course_data = [
                        'topics_count' => $course->topics_count,
                        'sub_topics_count' => $course->sub_topics_count,
                        'assessments_count' => $course->assessments_count,
                        'course_evaluations_count' => $course->course_evaluations_count,
                        'module_evaluations_count' => $course->module_evaluations_count,
                        'zoom_meetings_count' => $course->zoom_meetings_count,
                        'group_activities_count' => $course->group_activities_count,
                    ];

                    $learner_data = [
                        'total_done_topics' => $course->getTotalDoneTopics($learner->id),
                        'total_done_sub_topics' => $course->getTotalDoneSubTopics($learner->id),
                        'total_done_assessments' => $course->getTotalDoneAssessments($learner->id),
                        'total_done_course_evaluations' => $course->getTotalDoneCourseEvaluations($learner->id),
                        'total_done_module_evaluations' => $course->getTotalDoneModuleEvaluations($learner->id),
                        'total_attended_zoom_meetings' => $course->getTotalAttendedZoomMeetings($learner->id),
                        'total_done_group_activities' => $course->getTotalDoneGroupActivities($learner->id),
                        'total_time_spent' => $course->getTotalTimeSpent($course->id, $learner->id),
                    ];

                    $merged_data = (object) array_merge($course_data, $learner_data);

                    Log::info("USER COURSE". json_encode($merged_data));

                    $progress = $course->type_of_learning === 'BLENDED' ? Helper::calculateLearnerProgressPercentBlended($merged_data) : Helper::calculateLearnerProgressPercentElearning($merged_data);

                    StudentCourseProgress::updateOrCreate(
                        [
                            'user_id' => $learner->id,
                            'course_id' => $course->id
                        ],
                        array_merge($learner_data, ['progress' => $progress])
                    );

                    if ($progress >= 100) {
                        StudentCompleteCourse::firstOrCreate([
                            'user_id' => $learner->id,
                            'course_id' => $course->id
                        ]);
                    } else {
                        //delete if not yet completed --to fix data discrepancy
                        StudentCompleteCourse::where('user_id', $learner->id)
                            ->where('course_id', $course->id)
                            ->delete();

                        Certificate::where('user_id', $learner->id)
                            ->where('course_id', $course->id)
                            ->delete();

                    }
                }
            }
        }

        Log::info("Finished processing data");
    }
}
