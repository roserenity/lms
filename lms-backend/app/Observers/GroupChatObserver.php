<?php

namespace App\Observers;

use App\Models\GroupChat;

use Str;

class GroupChatObserver
{
    public function creating(GroupChat $groupChat)
    {
      $groupChat->uuid = Str::uuid();
    }

    /**
     * Handle the GroupChat "created" event.
     *
     * @param  \App\Models\GroupChat  $groupChat
     * @return void
     */
    public function created(GroupChat $groupChat)
    {
        //
    }

    /**
     * Handle the GroupChat "updated" event.
     *
     * @param  \App\Models\GroupChat  $groupChat
     * @return void
     */
    public function updated(GroupChat $groupChat)
    {
        //
    }

    /**
     * Handle the GroupChat "deleted" event.
     *
     * @param  \App\Models\GroupChat  $groupChat
     * @return void
     */
    public function deleted(GroupChat $groupChat)
    {
        //
    }

    /**
     * Handle the GroupChat "restored" event.
     *
     * @param  \App\Models\GroupChat  $groupChat
     * @return void
     */
    public function restored(GroupChat $groupChat)
    {
        //
    }

    /**
     * Handle the GroupChat "force deleted" event.
     *
     * @param  \App\Models\GroupChat  $groupChat
     * @return void
     */
    public function forceDeleted(GroupChat $groupChat)
    {
        //
    }
}
