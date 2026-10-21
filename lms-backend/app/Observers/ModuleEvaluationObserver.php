<?php

namespace App\Observers;

use App\Models\ModuleEvaluation;
use Str;
class ModuleEvaluationObserver
{
    public function creating(ModuleEvaluation $moduleEvaluation)
    {
      $moduleEvaluation->uuid = Str::uuid();
    }

    /**
     * Handle the ModuleEvaluation "created" event.
     *
     * @param  \App\Models\ModuleEvaluation  $moduleEvaluation
     * @return void
     */
    public function created(ModuleEvaluation $moduleEvaluation)
    {
        //
    }

    /**
     * Handle the ModuleEvaluation "updated" event.
     *
     * @param  \App\Models\ModuleEvaluation  $moduleEvaluation
     * @return void
     */
    public function updated(ModuleEvaluation $moduleEvaluation)
    {
        //
    }

    /**
     * Handle the ModuleEvaluation "deleted" event.
     *
     * @param  \App\Models\ModuleEvaluation  $moduleEvaluation
     * @return void
     */
    public function deleted(ModuleEvaluation $moduleEvaluation)
    {
        //
    }

    /**
     * Handle the ModuleEvaluation "restored" event.
     *
     * @param  \App\Models\ModuleEvaluation  $moduleEvaluation
     * @return void
     */
    public function restored(ModuleEvaluation $moduleEvaluation)
    {
        //
    }

    /**
     * Handle the ModuleEvaluation "force deleted" event.
     *
     * @param  \App\Models\ModuleEvaluation  $moduleEvaluation
     * @return void
     */
    public function forceDeleted(ModuleEvaluation $moduleEvaluation)
    {
        //
    }
}
