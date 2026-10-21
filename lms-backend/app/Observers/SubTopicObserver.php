<?php

namespace App\Observers;

use App\Models\SubTopic;
use Illuminate\Support\Str;

class SubTopicObserver
{
    /**
     * Handle the SubTopic "created" event.
     *
     * @param  \App\Models\SubTopic  $subTopic
     * @return void
     */
    public function creating(SubTopic $subTopic)
    {
      $subTopic->uuid = Str::uuid();
    }

    /**
     * Handle the SubTopic "updated" event.
     *
     * @param  \App\Models\SubTopic  $subTopic
     * @return void
     */
    public function updated(SubTopic $subTopic)
    {
        //
    }

    /**
     * Handle the SubTopic "deleted" event.
     *
     * @param  \App\Models\SubTopic  $subTopic
     * @return void
     */
    public function deleted(SubTopic $subTopic)
    {
        //
    }

    /**
     * Handle the SubTopic "restored" event.
     *
     * @param  \App\Models\SubTopic  $subTopic
     * @return void
     */
    public function restored(SubTopic $subTopic)
    {
        //
    }

    /**
     * Handle the SubTopic "force deleted" event.
     *
     * @param  \App\Models\SubTopic  $subTopic
     * @return void
     */
    public function forceDeleted(SubTopic $subTopic)
    {
        //
    }
}
