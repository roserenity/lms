<?php

namespace App\Jobs\Tenants;

use App\Models\AssessmentScore;
use App\Models\QuestionAnswer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use Log;

class ProcessAdjustLearnerAssessmentQuestionScoringJob implements ShouldQueue
{
    use Queueable;

    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        Log::info("Processing data for Tenant ID:". $this->tenantId);

        $question_answers = QuestionAnswer::where('assessment_question_id', 3941)->where('assessment_id', 569)->where('answer', 'choice_0')->where('score', 1)->get();
        
        foreach($question_answers as $question_answer) {
            $question_answer->score -= 1;
            $question_answer->save();

            $assessment_scores = AssessmentScore::where('user_id', $question_answer->user_id)->where('assessment_id', 569)->get();

            foreach($assessment_scores as $assessment_score) {
                if(in_array($question_answer->id, $assessment_score->question_answer_ids)) {
                    $assessment_score->score -= 1;
                    $assessment_score->save();
                }
            }
        }

        Log::info("Finished processing data");
    }
}
