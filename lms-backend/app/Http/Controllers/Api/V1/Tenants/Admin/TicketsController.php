<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Exports\Tenants\Admin\TicketsExport;

use App\Http\Controllers\Controller;
use App\Jobs\Tenants\ProcessAdminTicketsExport;
use App\Models\Exports;
use Illuminate\Http\Request;

use App\Services\Tenants\ImageService;

use App\Models\Ticket;
use App\Models\TicketMessage;

class TicketsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    public function showTickets(Request $request)
    {

        $searchParts = explode(' ', $request->search);

        $query = Ticket::with('user')->whereHas('user')
            ->when($request->category, function ($query) use ($request) {
                $query->where('concern_category', $request->category);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
            })
            ->when(!empty($request->search), function ($query) use ($request, $searchParts) {
                $query->whereHas('user', function ($query) use ($request, $searchParts) {
                    Helper::queryNameSearch($searchParts, $query);
                });
            })
            ->orderByRaw("FIELD(status, 1, 2, 3, 4, 5)")
            ->orderBy('created_at', 'DESC');

        $tickets = $query->paginate($request->paginate);
        
        $ticketCount = Ticket::with('user')->whereHas('user')->whereIn('status', [1, 2, 3])->count();
        return $this->sendResponse([ 'data' => $tickets, 'count' =>  $ticketCount], 'List of all tickets', 200);
    }

    public function showTicketCount(Request $request) {

        $statusCounts = [];

        for ($i = 0; $i < 5; $i++) {
            $statusCounts[$i] = Ticket::where('status', $i+1)->whereHas('user')
                ->when($request->category, function ($query) use ($request) {
                    $query->where('concern_category', $request->category);
                })
                // ->when($request->status, function ($query) use ($request) {
                //     $query->where('status', $request->status);
                // })
                ->when(($request->date_from || $request->date_to), function ($query) use ($request) {
                    FilterHelper::queryCreatedAt($query, $request->date_from, $request->date_to);
                })->count();
        }

        return $this->sendResponse(
            [
                'total_open_tickets' => $statusCounts[0],
                'total_pending_tickets' => $statusCounts[1],
                'total_on_hold_tickets' => $statusCounts[2],
                'total_solved_tickets' => $statusCounts[3],
                'total_closed_tickets' => $statusCounts[4]
            ],
            'Total Ticket Count per Status',
            200
        );

    }

    public function getTicketMessages(Request $request) 
    {

        $ticket = Ticket::where('uuid', $request->ticket)
            ->with(['user', 'ticket_messages' => fn($query) 
                    => $query->with([
                        'to_user',
                        'from_user',
                        'file_attachment'])])->first();

        return $this->sendResponse($ticket, 'List of ticket messages', 200);
    }

    public function store(Request $request){
        
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

    public function updateTicketStatus(Request $request)
    {
        $request->validate([
            'uuid' => 'required|exists:tickets,uuid',
            'status' => 'required',
        ]);

        $ticket = Ticket::where('uuid', $request->uuid)->firstOrFail();
        
        $ticket->update(['status' => $request->status]);

        return $this->sendResponse([], 'Status Updated!', 200);
    }
    
    public function exportTickets(Request $request) {
        
        $export = Exports::create([
            'user_id' => $this->authUser()->id,
            'type' => 'tickets',
            'status' => 'processing',
            'exported_at' => now(),
            'filename' => '',
            'path' => '',
            'url' => ''
        ]);
    
        ProcessAdminTicketsExport::dispatch(
            $export->id,
            $request->status,
            $request->category,
            $request->date_from,
            $request->date_to,
            $request->search,
        );
    
        return response()->json([
            'message' => 'Export has been queued',
            'status' => 'processing',
        ]);

        return new TicketsExport($request->status, $request->category, $request->date_from, $request->date_to, $request->search);  

    }
}
