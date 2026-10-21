<?php

namespace App\Observers;

use App\Models\CourseEvaluation;
use Str;

class CourseEvaluationObserver
{
    public function creating(CourseEvaluation $courseEvaluation)
    {
      $courseEvaluation->uuid = Str::uuid();
    }

    /**
     * Handle the CourseEvaluation "created" event.
     *
     * @param  \App\Models\CourseEvaluation  $courseEvaluation
     * @return void
     */
    public function created(CourseEvaluation $courseEvaluation)
    {
        //
    }

    /**
     * Handle the CourseEvaluation "updated" event.
     *
     * @param  \App\Models\CourseEvaluation  $courseEvaluation
     * @return void
     */
    public function updated(CourseEvaluation $courseEvaluation)
    {
        //
    }

    /**
     * Handle the CourseEvaluation "deleted" event.
     *
     * @param  \App\Models\CourseEvaluation  $courseEvaluation
     * @return void
     */
    public function deleted(CourseEvaluation $courseEvaluation)
    {
        //
    }

    /**
     * Handle the CourseEvaluation "restored" event.
     *
     * @param  \App\Models\CourseEvaluation  $courseEvaluation
     * @return void
     */
    public function restored(CourseEvaluation $courseEvaluation)
    {
        //
    }

    /**
     * Handle the CourseEvaluation "force deleted" event.
     *
     * @param  \App\Models\CourseEvaluation  $courseEvaluation
     * @return void
     */
    public function forceDeleted(CourseEvaluation $courseEvaluation)
    {
        //
    }
}
