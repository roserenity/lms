<?php
namespace App\Events;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Log;
class SendPrivateMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    private $data;
    private $receiver;
    private $sender;
    private $private_chat_uuid;

    public function __construct($data, $receiver, $sender, $private_chat_uuid)
    {
        $this->data = $data;
        $this->receiver = $receiver;
        $this->sender = $sender;
        $this->private_chat_uuid = $private_chat_uuid;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat-'.$this->receiver);
    }

    public function broadcastWith()
    {
        $data = (array) $this->data;
        $data['enrollee_id'] = $this->receiver;
        $data['private_chat_uuid'] = $this->private_chat_uuid;

        return [
            'data' => $data,
            'message' => $this->sender->full_name . ' sent you a message',
        ];
    }
}