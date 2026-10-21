<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Events\SendPrivateMessage;

use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\PrivateMessageRequest;
use App\Models\Enrollment;
use App\Services\Tenants\AttachmentService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;

use App\Models\PrivateChat;
use App\Models\PrivateMessage;
use App\Models\User;

use Log;

class PrivateChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function showContactList(Request $request)
    {
        $searchParts = explode(' ', request('search'));

        $course_ids = Enrollment::where('user_id', $this->authUser()->id)->pluck('course_id')->toArray();

        $users = User::select(['id', 'first_name', 'last_name', 'suffix', 'role'])
            ->where('id', '!=', $this->authUser()->id)
            ->with(['private_chat_as_creator' => function($private_chat_as_creator) {
                $private_chat_as_creator->where('user_id', $this->authUser()->id);
            }, 'private_chat_as_user' => function($private_chat_as_user) {
                $private_chat_as_user->where('creator_id', $this->authUser()->id);
            }])
            ->when(!empty(request('search')), function ($query) use ($searchParts) {
                Helper::queryNameSearch($searchParts, $query);
            })
            ->whereHas('enrollments', function ($query) use ($course_ids) {
                $query->whereIn('course_id', $course_ids);
            })
            ->orWhereHas('shared_courses', function ($query) use ($course_ids) {
                $query->whereIn('course_id', $course_ids);
            })
            ->orWhereHas('courses', function ($query) use ($course_ids) {
                $query->whereIn('id', $course_ids);
            })
            ->paginate(request('paginate'));

        return $this->sendResponse($users, 'List of all co-learners.', 200);
    }

    public function showPrivateChatList()
    {
        $private_chat_list = PrivateChat::with([
                'latest_private_message' => fn($latest_private_message) => $latest_private_message->select(['id', 'from_user_id', 'message_body', 'private_chat_id', 'to_user_id', 'uuid', 'created_at']),
                'creator' => fn($creator) => $creator->select(['id', 'first_name', 'last_name', 'suffix']),
                'user' => fn($user) => $user->select(['id', 'first_name', 'last_name', 'suffix'])
            ])
            ->where(function($query) {
                $query->where('creator_id', $this->authUser()->id)
                    ->orWhere('user_id', $this->authUser()->id);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return $this->sendResponse($private_chat_list, 'List of all private chat', 200);
    }

    public function showPrivateChat($private_chat_uuid)
    {
        $private_chat = PrivateChat::with(['private_messages' => function($private_messages) {
            return $private_messages->select(['id', 'from_user_id', 'message_body', 'private_chat_id', 'to_user_id', 'uuid', 'created_at'])
                ->with([
                    'attachments',
                    'sender' => fn($sender) => $sender->select(['id', 'first_name', 'last_name', 'suffix'])
                ])
                ->latest()
                ->paginate(15);
        }, 
            'creator' => fn($creator) => $creator->select(['id', 'first_name', 'last_name', 'suffix']),
            'user' => fn($user) => $user->select(['id', 'first_name', 'last_name', 'suffix'])
        ])
        ->where('uuid', $private_chat_uuid)->first();

        if (!$private_chat) {
            return $this->sendResponse([], 'No private message found.', 404);
        }

        return $this->sendResponse($private_chat, 'Private chat.', 200);
    }

    public function sendPrivateMessage(PrivateMessageRequest $request)
    {
        $latest_private_message = null;

        if (!$request->private_chat_id) {
            $private_chat = PrivateChat::create([
                'creator_id' => $request->from_user_id,
                'user_id' => $request->to_user_id
            ]);
        } else if ($request->private_chat_id) {
            $private_chat = PrivateChat::where('id', $request->private_chat_id)->with('latest_private_message')->first();

            if (!$private_chat) {
                return $this->sendResponse([], 'No private chat found.', 404);
            }

            Log::info(json_encode($private_chat));

            $latest_private_message = $private_chat->latest_private_message;
        }
        
        $private_chat->touch();
        $private_message = PrivateMessage::create($request->safe()->except('private_chat_id') + ['private_chat_id' => $private_chat->id]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file_attachment) {
                AttachmentService::uploadPrivateFileAttachment([
                    'model_id' => $private_message->id,
                    'model_type' => 'App\\Models\\PrivateMessage',
                    'private_chat_uuid' => $private_chat->uuid,
                    'private_message_uuid' => $private_message->uuid,
                    'file' => $file_attachment
                ]);
            }
        }

        $sender = User::select(['id', 'first_name', 'last_name', 'suffix'])->find($request->from_user_id);

        $receiver = User::select(['id', 'enrollee_id'])->find($request->to_user_id);

        event(new SendPrivateMessage($request->toArray(), $receiver->enrollee_id, $sender, $private_chat->uuid));

        return $this->sendResponse($private_chat->uuid, 'Message sent.', 201);
    }

    public function downloadAttachment(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $presignedUrl = S3Helper::generatePresignedDownloadUrl($request->url);

        return response()->json([
            'result' => $presignedUrl ? 'success' : 'failed',
            'downloadable_url' => $presignedUrl
        ]);
    }
}
