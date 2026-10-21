<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketReceiveNotification;
use App\Notifications\TicketStatusUpdateNotification;
use Str;
use Illuminate\Support\Facades\Notification;

use Log;

class TicketObserver
{
    public function creating(Ticket $ticket)
    {
      $ticket->uuid = Str::uuid();
    }

    /**
     * Handle the Ticket "created" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function created(Ticket $ticket)
    {

        if(auth()->guard('api')->user()->id != $ticket->user_id) {

            $admins = User::where('role', User::ROLE_ADMIN)->get(['id']);

            $currentUser = auth()->guard('api')->user();
        
            $data = [
                'name' => $currentUser->full_name,
                'photo' => $currentUser->image->url ?? null,
                'ticket_uuid' => $ticket->uuid
            ];
        
            Notification::send($admins, new TicketReceiveNotification($data));
        }

    }

    /**
     * Handle the Ticket "updated" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function updated(Ticket $ticket)
    {
        //
        if($ticket->isDirty('status') && $ticket->status != Ticket::TICKET_OPEN){

            $currentUser = auth()->guard('api')->user();
    
            $data = [
                'name' => $currentUser->full_name,
                'photo' => $currentUser->image->url ?? null,
                'ticket_uuid' => $ticket->uuid,
                'status' => $ticket->status
            ];
        
            $sender = User::find($ticket->user_id);

            Notification::send($sender, new TicketStatusUpdateNotification($data));
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function deleted(Ticket $ticket)
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function restored(Ticket $ticket)
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function forceDeleted(Ticket $ticket)
    {
        //
    }
}
