<?php

namespace App\Observers;

use App\Models\GroupMessage;

use Str;

class GroupMessageObserver
{
    public function creating(GroupMessage $groupMessage)
    {
        $groupMessage->uuid = Str::uuid();
    }

    /**
     * Handle the GroupMessage "created" event.
     *
     * @param  \App\Models\GroupMessage  $groupMessage
     * @return void
     */
    public function created(GroupMessage $groupMessage)
    {
        //
    }

    /**
     * Handle the GroupMessage "updated" event.
     *
     * @param  \App\Models\GroupMessage  $groupMessage
     * @return void
     */
    public function updated(GroupMessage $groupMessage)
    {
        //
    }

    /**
     * Handle the GroupMessage "deleted" event.
     *
     * @param  \App\Models\GroupMessage  $groupMessage
     * @return void
     */
    public function deleted(GroupMessage $groupMessage)
    {
        //
    }

    /**
     * Handle the GroupMessage "restored" event.
     *
     * @param  \App\Models\GroupMessage  $groupMessage
     * @return void
     */
    public function restored(GroupMessage $groupMessage)
    {
        //
    }

    /**
     * Handle the GroupMessage "force deleted" event.
     *
     * @param  \App\Models\GroupMessage  $groupMessage
     * @return void
     */
    public function forceDeleted(GroupMessage $groupMessage)
    {
        //
    }
}
