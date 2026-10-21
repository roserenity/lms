<?php

namespace App\Observers;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentScore;

use Str;

class AssessmentQuestionObserver
{
    public function creating(AssessmentQuestion $assessmentQuestion)
    {
      $assessmentQuestion->uuid = Str::uuid();
    }

    /**
     * Handle the AssessmentQuestion "created" event.
     *
     * @param  \App\Models\AssessmentQuestion  $assessmentQuestion
     * @return void
     */
    public function created(AssessmentQuestion $assessmentQuestion)
    {
        //
        $this->updateAssessmentTotalPoints($assessmentQuestion->assessment_id);
    }

    /**
     * Handle the AssessmentQuestion "updated" event.
     *
     * @param  \App\Models\AssessmentQuestion  $assessmentQuestion
     * @return void
     */
    public function updated(AssessmentQuestion $assessmentQuestion)
    {
        //
        if($assessmentQuestion->isDirty('score'))
        {
            $this->updateAssessmentTotalPoints($assessmentQuestion->assessment_id);
        }
    }

    /**
     * Handle the AssessmentQuestion "deleted" event.
     *
     * @param  \App\Models\AssessmentQuestion  $assessmentQuestion
     * @return void
     */
    public function deleted(AssessmentQuestion $assessmentQuestion)
    {
        //
        $this->updateAssessmentTotalPoints($assessmentQuestion->assessment_id);
    }

    /**
     * Handle the AssessmentQuestion "restored" event.
     *
     * @param  \App\Models\AssessmentQuestion  $assessmentQuestion
     * @return void
     */
    public function restored(AssessmentQuestion $assessmentQuestion)
    {
        //
    }

    /**
     * Handle the AssessmentQuestion "force deleted" event.
     *
     * @param  \App\Models\AssessmentQuestion  $assessmentQuestion
     * @return void
     */
    public function forceDeleted(AssessmentQuestion $assessmentQuestion)
    {
        //
    }

    private function updateAssessmentScore($assessment_id)
    {
        $assessment = Assessment::where('id', $assessment_id)->select(['passing_score', 'total_points'])->first();

        $assessments_scores = AssessmentScore::where('assessment_id', $assessment_id)->get();

        foreach($assessments_scores as $assessments_score)
        {

            // Determine the status based on the passing score
            if ($assessment->passing_score)
            {
                if ($assessments_score->assessment_score_percentage >= $assessment->passing_score)
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
            
            $assessments_score->save();

        }
    }
    
    private function updateAssessmentTotalPoints($assessment_id)
    {
        $assessment = Assessment::find($assessment_id);
        $sum = $assessment->assessment_questions->sum('score');
        $assessment->update(['total_points' => $sum]);

            
        $this->updateAssessmentScore($assessment_id);
    }
}
