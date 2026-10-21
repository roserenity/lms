<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\Tenants\ImageService;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketMessage;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function index()
    {
        $tickets = Ticket::where('user_id', $this->authUser()->id)
            ->when(!empty(request('category')), function ($query) {
                $query->where('concern_category', request('category'));
            })
            ->when(!empty(request('status')), function ($query) {
                $query->where('status', request('status'));
            })->orderBy('created_at', 'DESC')->paginate(request('paginate'));

        return $this->sendResponse($tickets, 'List of all tickets.' . $this->authUser()->id, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'concern_category' => 'required|in:platform,content,assessment,others',
            'content' => 'required',
            'file_attachment.*' => 'nullable|mimes:jpg,jpeg,png'
        ]);

        $user = User::where('email', $request->email)->first();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'concern_category' => $request->concern_category,
            'content' => $request->content
        ]);

        if ($request->hasFile('file_attachment')) {
            foreach ($request->file('file_attachment') as $file_attachment) {
                ImageService::uploadTicketFileAttachment([
                    'model_id' => $ticket->id,
                    'model_type' => 'App\\Models\\Ticket',
                    'file_attachment' => $file_attachment
                ]);
            }
        }

        return $this->sendResponse([], 'Ticket submitted.', 201);
    }

    public function getTicketMessages(Request $request){

        $ticket = Ticket::where('uuid', $request->ticket)->with(['ticket_messages' => fn($query) => $query->with(['to_user', 'from_user', 'file_attachment'])])->first();
            
        return $this->sendResponse($ticket, 'List of ticket messages', 200);
    }

    public function submitTicketMessage(Request $request){
        
        $request->validate([
            'uuid' => 'required|exists:tickets,uuid',
            'reply' => 'required',
            'file_attachment.*' => 'nullable|mimes:jpg,jpeg,png',
            'to_user_id' => 'required|exists:users,id',
            'from_user_id' => 'required|exists:users,id',
        ]);

        $ticket = Ticket::where('uuid', $request->uuid)->first();

        $ticketMessage = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'to_user_id' => $request->to_user_id,
            'from_user_id' => $request->from_user_id,
            'content' => $request->reply
        ]);
        
        if ($request->hasFile('file_attachment')) {
            foreach ($request->file('file_attachment') as $file_attachment) {
                ImageService::uploadTicketReplyFileAttachment([
                    'model_id' => $ticketMessage->id,
                    'model_type' => 'App\\Models\\TicketMessage',
                    'file_attachment' => $file_attachment
                ]);
            }
        }

            
        return $this->sendResponse([], 'Replied!', 200);
    }

}
