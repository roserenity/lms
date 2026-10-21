<?php

namespace App\Jobs\Tenants;

use App\Models\User;
use App\Models\Course;
use App\Models\StudentCourseEvaluation;
use App\Models\StudentModuleEvaluation;
use App\Models\StudentModuleProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Helpers\Helper;

use Log;
use Stancl\Tenancy\Concerns\HasATenantArgument;

class SaveUserEvaluationProgress implements ShouldQueue
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

        Log::info("Processing evaluation data for Tenant ID:". $this->tenantId);

        $learners = User::query()->select('id')->with('enrollments')->get();

        foreach ($learners as $learner) {
            foreach($learner->enrollments as $enrollment) {

                $course = Course::query()->select(['id'])->where('id', $enrollment->course_id)
                    ->with(['course_evaluations', 'module_evaluations'])
                    ->first();
                
                if($course) {

                    foreach($course->course_evaluations as $course_evaluation ) {
                        
                        $student_course_evaluation = StudentCourseEvaluation::where('course_evaluation_id', $course_evaluation->id)->where('user_id', $learner->id)->exists();

                        if($student_course_evaluation) {
                            StudentModuleProgress::firstOrCreate([
                                'model_id' => $course_evaluation->id,
                                'model_type' => 'App\\Models\\CourseEvaluation',
                                'course_id' => $course->id,
                                'user_id' => $learner->id
                            ]);
                        }

                    }
                    
                    foreach($course->module_evaluations as $module_evaluation ) {
                        
                        $student_module_evaluation = StudentModuleEvaluation::where('module_evaluation_id', $module_evaluation->id)->where('user_id', $learner->id)->exists();

                        if($student_module_evaluation) {
                            StudentModuleProgress::firstOrCreate([
                                'model_id' => $module_evaluation->id,
                                'model_type' => 'App\\Models\\ModuleEvaluation',
                                'course_id' => $course->id,
                                'user_id' => $learner->id
                            ]);
                        }

                    }

                }
            }
        }

        Log::info("Finished processing data");
    }
}
