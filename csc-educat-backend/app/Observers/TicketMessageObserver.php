<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Notifications\TicketMessageNotification;
use Str;
use Illuminate\Support\Facades\Notification;

use Log;

class TicketMessageObserver
{
    //
    public function creating(TicketMessage $ticket_message)
    {
      $ticket_message->uuid = Str::uuid();
    }/**
     * Handle the TicketMessage "created" event.
     *
     * @param  \App\Models\TicketMessage $ticket_message
     * @return void
     */
    public function created(TicketMessage $ticket_message)
    {

        $ticket = Ticket::findOrFail($ticket_message->ticket_id);
        $current_user = auth()->guard('api')->user();

        $unique_replied_ids = TicketMessage::where('ticket_id', $ticket_message->ticket_id)
            ->select('to_user_id', 'from_user_id')->get()
            ->flatMap(function ($msg) {
                return [$msg->to_user_id, $msg->from_user_id];
            })
            ->filter(fn ($id) => $id !== $ticket->user_id)
            ->unique()
            ->values();

        if($ticket->user_id == $ticket_message->from_user_id) {
            if ($unique_replied_ids->isEmpty()) {
                $admins = User::where('role', User::ROLE_ADMIN)->get(['id']);
    
                $data = [
                    'name' => $current_user->full_name,
                    'photo' => $current_user->image->url ?? null,
                    'ticket_uuid' => $ticket->uuid,
                    'text' => 'replied to their ticket'
                ];
    
                Notification::send($admins, new TicketMessageNotification($data));
            } else {
                // Notify all previously involved admins (excluding learner)
                $admins = User::whereIn('id', $unique_replied_ids)->get(['id']);
    
                $data = [
                    'name' => $current_user->full_name,
                    'photo' => $current_user->image->url ?? null,
                    'ticket_uuid' => $ticket->uuid,
                    'text' => 'replied to their ticket'
                ];
    
                Notification::send($admins, new TicketMessageNotification($data));
            }
        } else {
            
            $replier = User::find($ticket_message->from_user_id);
        
            $data = [
                'name' => $replier->full_name,
                'photo' => $replier->image->url ?? null,
                'ticket_uuid' => $ticket->uuid,
                'text' => 'replied to your ticket'
            ];
        
            Notification::send($ticket->user, new TicketMessageNotification($data));
        }

    }

    /**
     * Handle the TicketMessage "updated" event.
     *
     * @param  \App\Models\TicketMessage $ticket_message
     * @return void
     */
    public function updated(TicketMessage $ticket_message)
    {
        //
    }

    /**
     * Handle the TicketMessage "deleted" event.
     *
     * @param  \App\Models\TicketMessage  $ticket_message
     * @return void
     */
    public function deleted(TicketMessage $ticket_message)
    {
        //
    }

    /**
     * Handle the TicketMessage "restored" event.
     *
     * @param  \App\Models\TicketMessage  $ticket_message
     * @return void
     */
    public function restored(TicketMessage $ticket_message)
    {
        //
    }

    /**
     * Handle the TicketMessage "force deleted" event.
     *
     * @param  \App\Models\TicketMessage  $ticket_message
     * @return void
     */
    public function forceDeleted(TicketMessage $ticket_message)
    {
        //
    }
}
