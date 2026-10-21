<?php

namespace App\Observers;

use App\Models\StudentCourseEvaluation;
use Str;

class StudentCourseEvaluationObserver
{
    public function creating(StudentCourseEvaluation $studentCourseEvaluation)
    {
      $studentCourseEvaluation->uuid = Str::uuid();
    }

    /**
     * Handle the StudentCourseEvaluation "created" event.
     *
     * @param  \App\Models\StudentCourseEvaluation  $studentCourseEvaluation
     * @return void
     */
    public function created(StudentCourseEvaluation $studentCourseEvaluation)
    {
        //
    }

    /**
     * Handle the StudentCourseEvaluation "updated" event.
     *
     * @param  \App\Models\StudentCourseEvaluation  $studentCourseEvaluation
     * @return void
     */
    public function updated(StudentCourseEvaluation $studentCourseEvaluation)
    {
        //
    }

    /**
     * Handle the StudentCourseEvaluation "deleted" event.
     *
     * @param  \App\Models\StudentCourseEvaluation  $studentCourseEvaluation
     * @return void
     */
    public function deleted(StudentCourseEvaluation $studentCourseEvaluation)
    {
        //
    }

    /**
     * Handle the StudentCourseEvaluation "restored" event.
     *
     * @param  \App\Models\StudentCourseEvaluation  $studentCourseEvaluation
     * @return void
     */
    public function restored(StudentCourseEvaluation $studentCourseEvaluation)
    {
        //
    }

    /**
     * Handle the StudentCourseEvaluation "force deleted" event.
     *
     * @param  \App\Models\StudentCourseEvaluation  $studentCourseEvaluation
     * @return void
     */
    public function forceDeleted(StudentCourseEvaluation $studentCourseEvaluation)
    {
        //
    }
}
