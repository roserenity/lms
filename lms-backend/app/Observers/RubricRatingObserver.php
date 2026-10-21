<?php

namespace App\Observers;

use App\Models\RubricRating;

use Str;

class RubricRatingObserver
{
    public function creating(RubricRating $rubricRating)
    {
      $rubricRating->uuid = Str::uuid();
    }

    /**
     * Handle the RubricRating "created" event.
     *
     * @param  \App\Models\RubricRating  $rubricRating
     * @return void
     */
    public function created(RubricRating $rubricRating)
    {
        //
    }

    /**
     * Handle the RubricRating "updated" event.
     *
     * @param  \App\Models\RubricRating  $rubricRating
     * @return void
     */
    public function updated(RubricRating $rubricRating)
    {
        //
    }

    /**
     * Handle the RubricRating "deleted" event.
     *
     * @param  \App\Models\RubricRating  $rubricRating
     * @return void
     */
    public function deleted(RubricRating $rubricRating)
    {
        //
    }

    /**
     * Handle the RubricRating "restored" event.
     *
     * @param  \App\Models\RubricRating  $rubricRating
     * @return void
     */
    public function restored(RubricRating $rubricRating)
    {
        //
    }

    /**
     * Handle the RubricRating "force deleted" event.
     *
     * @param  \App\Models\RubricRating  $rubricRating
     * @return void
     */
    public function forceDeleted(RubricRating $rubricRating)
    {
        //
    }
}
