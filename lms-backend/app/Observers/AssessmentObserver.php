<?php

namespace App\Observers;

use App\Models\Assessment;
use App\Models\AssessmentScore;

use Str;

class AssessmentObserver
{
    public function creating(Assessment $assessment)
    {
      $assessment->uuid = Str::uuid();
    }

    /**
     * Handle the Assessment "created" event.
     *
     * @param  \App\Models\Assessment  $assessment
     * @return void
     */
    public function created(Assessment $assessment)
    {
        //
    }

    /**
     * Handle the Assessment "updated" event.
     *
     * @param  \App\Models\Assessment  $assessment
     * @return void
     */
    public function updated(Assessment $assessment)
    {
        //
        if($assessment->isDirty('passing_score'))
        {
            $assessments_scores = AssessmentScore::where('assessment_id', $assessment->id)->get();
    
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
    }

    /**
     * Handle the Assessment "deleted" event.
     *
     * @param  \App\Models\Assessment  $assessment
     * @return void
     */
    public function deleted(Assessment $assessment)
    {
        //
    }

    /**
     * Handle the Assessment "restored" event.
     *
     * @param  \App\Models\Assessment  $assessment
     * @return void
     */
    public function restored(Assessment $assessment)
    {
        //
    }

    /**
     * Handle the Assessment "force deleted" event.
     *
     * @param  \App\Models\Assessment  $assessment
     * @return void
     */
    public function forceDeleted(Assessment $assessment)
    {
        //
    }
}
