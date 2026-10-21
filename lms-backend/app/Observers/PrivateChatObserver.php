<?php

namespace App\Observers;

use App\Models\PrivateChat;

use Str;

class PrivateChatObserver
{
    public function creating(PrivateChat $privateChat)
    {
        $privateChat->uuid = Str::uuid();
    }

    /**
     * Handle the PrivateChat "created" event.
     *
     * @param  \App\Models\PrivateChat  $privateChat
     * @return void
     */
    public function created(PrivateChat $privateChat)
    {
        //
    }

    /**
     * Handle the PrivateChat "updated" event.
     *
     * @param  \App\Models\PrivateChat  $privateChat
     * @return void
     */
    public function updated(PrivateChat $privateChat)
    {
        //
    }

    /**
     * Handle the PrivateChat "deleted" event.
     *
     * @param  \App\Models\PrivateChat  $privateChat
     * @return void
     */
    public function deleted(PrivateChat $privateChat)
    {
        //
    }

    /**
     * Handle the PrivateChat "restored" event.
     *
     * @param  \App\Models\PrivateChat  $privateChat
     * @return void
     */
    public function restored(PrivateChat $privateChat)
    {
        //
    }

    /**
     * Handle the PrivateChat "force deleted" event.
     *
     * @param  \App\Models\PrivateChat  $privateChat
     * @return void
     */
    public function forceDeleted(PrivateChat $privateChat)
    {
        //
    }
}
