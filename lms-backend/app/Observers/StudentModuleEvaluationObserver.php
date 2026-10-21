<?php

namespace App\Observers;

use App\Models\StudentModuleEvaluation;
use Str;

class StudentModuleEvaluationObserver
{
    public function creating(StudentModuleEvaluation $studentModuleEvaluation)
    {
      $studentModuleEvaluation->uuid = Str::uuid();
    }

    /**
     * Handle the StudentModuleEvaluation "created" event.
     *
     * @param  \App\Models\StudentModuleEvaluation  $studentModuleEvaluation
     * @return void
     */
    public function created(StudentModuleEvaluation $studentModuleEvaluation)
    {
        //
    }

    /**
     * Handle the StudentModuleEvaluation "updated" event.
     *
     * @param  \App\Models\StudentModuleEvaluation  $studentModuleEvaluation
     * @return void
     */
    public function updated(StudentModuleEvaluation $studentModuleEvaluation)
    {
        //
    }

    /**
     * Handle the StudentModuleEvaluation "deleted" event.
     *
     * @param  \App\Models\StudentModuleEvaluation  $studentModuleEvaluation
     * @return void
     */
    public function deleted(StudentModuleEvaluation $studentModuleEvaluation)
    {
        //
    }

    /**
     * Handle the StudentModuleEvaluation "restored" event.
     *
     * @param  \App\Models\StudentModuleEvaluation  $studentModuleEvaluation
     * @return void
     */
    public function restored(StudentModuleEvaluation $studentModuleEvaluation)
    {
        //
    }

    /**
     * Handle the StudentModuleEvaluation "force deleted" event.
     *
     * @param  \App\Models\StudentModuleEvaluation  $studentModuleEvaluation
     * @return void
     */
    public function forceDeleted(StudentModuleEvaluation $studentModuleEvaluation)
    {
        //
    }
}
