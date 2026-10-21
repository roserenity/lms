<?php

namespace App\Jobs\Tenants;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use Log;
use Stancl\Tenancy\Concerns\HasATenantArgument;
use App\Helpers\Helper;

class SetLearnerAssessmentStatus implements ShouldQueue
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

        $assessments_scores = AssessmentScore::all();
    
        $assessments = Assessment::all()->keyBy('id');

        foreach($assessments_scores as $assessments_score)
        {
            $assessment = $assessments->get($assessments_score->assessment_id);

            if ($assessment) {
                // Calculate learner percentage
                $learner_percentage = number_format(
                    Helper::calculatePercentage(floatval($assessments_score->score), floatval($assessment->total_points)), 2
                );

                // Determine the status based on the passing score
                if ($assessment->passing_score)
                {
                    if ($learner_percentage >= $assessment->passing_score)
                    {
                        $assessments_score->status = 'PASSED';
                    }
                    else
                    {
                        $assessments_score->status = 'FAILED';
                    }
                }
                else
                {
                    $assessments_score->status = 'FAILED';
                }

                // Save the updated status
                $assessments_score->save();
            }
        }
        
        Log::info("Finished processing data");
    }
}
