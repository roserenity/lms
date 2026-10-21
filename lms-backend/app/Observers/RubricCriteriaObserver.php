<?php

namespace App\Observers;

use App\Models\RubricCriteria;

use Str;

class RubricCriteriaObserver
{
    public function creating(RubricCriteria $rubricCriteria)
    {
      $rubricCriteria->uuid = Str::uuid();
    }

    /**
     * Handle the RubricCriteria "created" event.
     *
     * @param  \App\Models\RubricCriteria  $rubricCriteria
     * @return void
     */
    public function created(RubricCriteria $rubricCriteria)
    {
        //
    }

    /**
     * Handle the RubricCriteria "updated" event.
     *
     * @param  \App\Models\RubricCriteria  $rubricCriteria
     * @return void
     */
    public function updated(RubricCriteria $rubricCriteria)
    {
        //
    }

    /**
     * Handle the RubricCriteria "deleted" event.
     *
     * @param  \App\Models\RubricCriteria  $rubricCriteria
     * @return void
     */
    public function deleted(RubricCriteria $rubricCriteria)
    {
        //
    }

    /**
     * Handle the RubricCriteria "restored" event.
     *
     * @param  \App\Models\RubricCriteria  $rubricCriteria
     * @return void
     */
    public function restored(RubricCriteria $rubricCriteria)
    {
        //
    }

    /**
     * Handle the RubricCriteria "force deleted" event.
     *
     * @param  \App\Models\RubricCriteria  $rubricCriteria
     * @return void
     */
    public function forceDeleted(RubricCriteria $rubricCriteria)
    {
        //
    }
}
