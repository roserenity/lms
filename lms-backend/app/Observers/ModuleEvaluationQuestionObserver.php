<?php

namespace App\Observers;

use App\Models\ModuleEvaluationQuestion;
use Str;
class ModuleEvaluationQuestionObserver
{
    public function creating(ModuleEvaluationQuestion $moduleEvaluationQuestion)
    {
      $moduleEvaluationQuestion->uuid = Str::uuid();
    }

    /**
     * Handle the ModuleEvaluationQuestion "created" event.
     *
     * @param  \App\Models\ModuleEvaluationQuestion  $moduleEvaluationQuestion
     * @return void
     */
    public function created(ModuleEvaluationQuestion $moduleEvaluationQuestion)
    {
        //
    }

    /**
     * Handle the ModuleEvaluationQuestion "updated" event.
     *
     * @param  \App\Models\ModuleEvaluationQuestion  $moduleEvaluationQuestion
     * @return void
     */
    public function updated(ModuleEvaluationQuestion $moduleEvaluationQuestion)
    {
        //
    }

    /**
     * Handle the ModuleEvaluationQuestion "deleted" event.
     *
     * @param  \App\Models\ModuleEvaluationQuestion  $moduleEvaluationQuestion
     * @return void
     */
    public function deleted(ModuleEvaluationQuestion $moduleEvaluationQuestion)
    {
        //
    }

    /**
     * Handle the ModuleEvaluationQuestion "restored" event.
     *
     * @param  \App\Models\ModuleEvaluationQuestion  $moduleEvaluationQuestion
     * @return void
     */
    public function restored(ModuleEvaluationQuestion $moduleEvaluationQuestion)
    {
        //
    }

    /**
     * Handle the ModuleEvaluationQuestion "force deleted" event.
     *
     * @param  \App\Models\ModuleEvaluationQuestion  $moduleEvaluationQuestion
     * @return void
     */
    public function forceDeleted(ModuleEvaluationQuestion $moduleEvaluationQuestion)
    {
        //
    }
}
