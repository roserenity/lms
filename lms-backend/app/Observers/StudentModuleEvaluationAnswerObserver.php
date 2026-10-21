<?php

namespace App\Observers;

use App\Models\StudentModuleEvaluationAnswer;
use Str;

class StudentModuleEvaluationAnswerObserver
{
    public function creating(StudentModuleEvaluationAnswer $studentModuleEvaluationAnswer)
    {
      $studentModuleEvaluationAnswer->uuid = Str::uuid();
    }

    /**
     * Handle the StudentModuleEvaluationAnswer "created" event.
     *
     * @param  \App\Models\StudentModuleEvaluationAnswer  $studentModuleEvaluationAnswer
     * @return void
     */
    public function created(StudentModuleEvaluationAnswer $studentModuleEvaluationAnswer)
    {
        //
    }

    /**
     * Handle the StudentModuleEvaluationAnswer "updated" event.
     *
     * @param  \App\Models\StudentModuleEvaluationAnswer  $studentModuleEvaluationAnswer
     * @return void
     */
    public function updated(StudentModuleEvaluationAnswer $studentModuleEvaluationAnswer)
    {
        //
    }

    /**
     * Handle the StudentModuleEvaluationAnswer "deleted" event.
     *
     * @param  \App\Models\StudentModuleEvaluationAnswer  $studentModuleEvaluationAnswer
     * @return void
     */
    public function deleted(StudentModuleEvaluationAnswer $studentModuleEvaluationAnswer)
    {
        //
    }

    /**
     * Handle the StudentModuleEvaluationAnswer "restored" event.
     *
     * @param  \App\Models\StudentModuleEvaluationAnswer  $studentModuleEvaluationAnswer
     * @return void
     */
    public function restored(StudentModuleEvaluationAnswer $studentModuleEvaluationAnswer)
    {
        //
    }

    /**
     * Handle the StudentModuleEvaluationAnswer "force deleted" event.
     *
     * @param  \App\Models\StudentModuleEvaluationAnswer  $studentModuleEvaluationAnswer
     * @return void
     */
    public function forceDeleted(StudentModuleEvaluationAnswer $studentModuleEvaluationAnswer)
    {
        //
    }
}
