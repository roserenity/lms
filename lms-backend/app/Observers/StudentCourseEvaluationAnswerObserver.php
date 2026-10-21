<?php

namespace App\Observers;

use App\Models\StudentCourseEvaluationAnswer;
use Str;

class StudentCourseEvaluationAnswerObserver
{
    public function creating(StudentCourseEvaluationAnswer $studentCourseEvaluationAnswer)
    {
      $studentCourseEvaluationAnswer->uuid = Str::uuid();
    }

    /**
     * Handle the StudentCourseEvaluationAnswer "created" event.
     *
     * @param  \App\Models\StudentCourseEvaluationAnswer  $studentCourseEvaluationAnswer
     * @return void
     */
    public function created(StudentCourseEvaluationAnswer $studentCourseEvaluationAnswer)
    {
        //
    }

    /**
     * Handle the StudentCourseEvaluationAnswer "updated" event.
     *
     * @param  \App\Models\StudentCourseEvaluationAnswer  $studentCourseEvaluationAnswer
     * @return void
     */
    public function updated(StudentCourseEvaluationAnswer $studentCourseEvaluationAnswer)
    {
        //
    }

    /**
     * Handle the StudentCourseEvaluationAnswer "deleted" event.
     *
     * @param  \App\Models\StudentCourseEvaluationAnswer  $studentCourseEvaluationAnswer
     * @return void
     */
    public function deleted(StudentCourseEvaluationAnswer $studentCourseEvaluationAnswer)
    {
        //
    }

    /**
     * Handle the StudentCourseEvaluationAnswer "restored" event.
     *
     * @param  \App\Models\StudentCourseEvaluationAnswer  $studentCourseEvaluationAnswer
     * @return void
     */
    public function restored(StudentCourseEvaluationAnswer $studentCourseEvaluationAnswer)
    {
        //
    }

    /**
     * Handle the StudentCourseEvaluationAnswer "force deleted" event.
     *
     * @param  \App\Models\StudentCourseEvaluationAnswer  $studentCourseEvaluationAnswer
     * @return void
     */
    public function forceDeleted(StudentCourseEvaluationAnswer $studentCourseEvaluationAnswer)
    {
        //
    }
}
