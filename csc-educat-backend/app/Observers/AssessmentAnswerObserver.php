<?php

namespace App\Observers;

use App\Models\AssessmentAnswer;

use Str;

class AssessmentAnswerObserver
{
    public function creating(AssessmentAnswer $assessmentAnswer)
    {
      $assessmentAnswer->uuid = Str::uuid();
    }

    /**
     * Handle the AssessmentAnswer "created" event.
     *
     * @param  \App\Models\AssessmentAnswer  $assessmentAnswer
     * @return void
     */
    public function created(AssessmentAnswer $assessmentAnswer)
    {
        //
    }

    /**
     * Handle the AssessmentAnswer "updated" event.
     *
     * @param  \App\Models\AssessmentAnswer  $assessmentAnswer
     * @return void
     */
    public function updated(AssessmentAnswer $assessmentAnswer)
    {
        //
    }

    /**
     * Handle the AssessmentAnswer "deleted" event.
     *
     * @param  \App\Models\AssessmentAnswer  $assessmentAnswer
     * @return void
     */
    public function deleted(AssessmentAnswer $assessmentAnswer)
    {
        //
    }

    /**
     * Handle the AssessmentAnswer "restored" event.
     *
     * @param  \App\Models\AssessmentAnswer  $assessmentAnswer
     * @return void
     */
    public function restored(AssessmentAnswer $assessmentAnswer)
    {
        //
    }

    /**
     * Handle the AssessmentAnswer "force deleted" event.
     *
     * @param  \App\Models\AssessmentAnswer  $assessmentAnswer
     * @return void
     */
    public function forceDeleted(AssessmentAnswer $assessmentAnswer)
    {
        //
    }
}
