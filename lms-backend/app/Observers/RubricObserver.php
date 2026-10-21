<?php

namespace App\Observers;

use App\Models\Rubric;

use Str;

class RubricObserver
{
    public function creating(Rubric $rubric)
    {
      $rubric->uuid = Str::uuid();
    }

    /**
     * Handle the Rubric "created" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function created(Rubric $rubric)
    {
        //
    }

    /**
     * Handle the Rubric "updated" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function updated(Rubric $rubric)
    {
        //
    }

    /**
     * Handle the Rubric "deleted" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function deleted(Rubric $rubric)
    {
        //
    }

    /**
     * Handle the Rubric "restored" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function restored(Rubric $rubric)
    {
        //
    }

    /**
     * Handle the Rubric "force deleted" event.
     *
     * @param  \App\Models\Rubric  $rubric
     * @return void
     */
    public function forceDeleted(Rubric $rubric)
    {
        //
    }
}
