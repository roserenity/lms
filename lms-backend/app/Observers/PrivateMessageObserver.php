<?php

namespace App\Observers;

use Illuminate\Support\Facades\Notification;

use App\Models\PrivateMessage;
use App\Models\Enrollment;
use App\Models\User;

use App\Notifications\PrivateMessageNotification;

use Str;

class PrivateMessageObserver
{
    public function creating(PrivateMessage $privateMessage)
    {
        $privateMessage->uuid = Str::uuid();
    }

    /**
     * Handle the PrivateMessage "created" event.
     *
     * @param  \App\Models\PrivateMessage  $privateMessage
     * @return void
     */
    public function created(PrivateMessage $privateMessage)
    {
        $data = [
            'name' => auth()->guard('api')->user()->full_name,
            'photo' => auth()->guard('api')->user()->image->url ?? null,
            'private_chat_uuid' => $privateMessage->private_chat->uuid
        ];

        $user = User::where('id', $privateMessage->to_user_id)->firstOrFail();

        Notification::send($user, new PrivateMessageNotification($data));
    }

    /**
     * Handle the PrivateMessage "updated" event.
     *
     * @param  \App\Models\PrivateMessage  $privateMessage
     * @return void
     */
    public function updated(PrivateMessage $privateMessage)
    {
        //
    }

    /**
     * Handle the PrivateMessage "deleted" event.
     *
     * @param  \App\Models\PrivateMessage  $privateMessage
     * @return void
     */
    public function deleted(PrivateMessage $privateMessage)
    {
        //
    }

    /**
     * Handle the PrivateMessage "restored" event.
     *
     * @param  \App\Models\PrivateMessage  $privateMessage
     * @return void
     */
    public function restored(PrivateMessage $privateMessage)
    {
        //
    }

    /**
     * Handle the PrivateMessage "force deleted" event.
     *
     * @param  \App\Models\PrivateMessage  $privateMessage
     * @return void
     */
    public function forceDeleted(PrivateMessage $privateMessage)
    {
        //
    }
}
