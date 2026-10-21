<?php

namespace App\Observers;

use App\Models\CourseEvaluationQuestion;
use Str;
class CourseEvaluationQuestionObserver
{
    public function creating(CourseEvaluationQuestion $courseEvaluationQuestion)
    {
      $courseEvaluationQuestion->uuid = Str::uuid();
    }

    /**
     * Handle the CourseEvaluationQuestion "created" event.
     *
     * @param  \App\Models\CourseEvaluationQuestion  $courseEvaluationQuestion
     * @return void
     */
    public function created(CourseEvaluationQuestion $courseEvaluationQuestion)
    {
        //
    }

    /**
     * Handle the CourseEvaluationQuestion "updated" event.
     *
     * @param  \App\Models\CourseEvaluationQuestion  $courseEvaluationQuestion
     * @return void
     */
    public function updated(CourseEvaluationQuestion $courseEvaluationQuestion)
    {
        //
    }

    /**
     * Handle the CourseEvaluationQuestion "deleted" event.
     *
     * @param  \App\Models\CourseEvaluationQuestion  $courseEvaluationQuestion
     * @return void
     */
    public function deleted(CourseEvaluationQuestion $courseEvaluationQuestion)
    {
        //
    }

    /**
     * Handle the CourseEvaluationQuestion "restored" event.
     *
     * @param  \App\Models\CourseEvaluationQuestion  $courseEvaluationQuestion
     * @return void
     */
    public function restored(CourseEvaluationQuestion $courseEvaluationQuestion)
    {
        //
    }

    /**
     * Handle the CourseEvaluationQuestion "force deleted" event.
     *
     * @param  \App\Models\CourseEvaluationQuestion  $courseEvaluationQuestion
     * @return void
     */
    public function forceDeleted(CourseEvaluationQuestion $courseEvaluationQuestion)
    {
        //
    }
}
