<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Helpers\Helper;
use App\Helpers\S3Helper;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Illuminate\Http\Request;

use App\Http\Requests\Tenants\GroupChatRequest;
use App\Http\Requests\Tenants\GroupChatMessageRequest;
use App\Http\Requests\Tenants\UpdateGroupChatPhotoRequest;

use App\Services\Tenants\AttachmentService;

use App\Models\GroupChat;
use App\Models\GroupMessage;
use App\Models\User;

use App\Services\Tenants\ImageService;
use App\Notifications\GroupMessageNotification;

class GroupChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function createGroupChat(GroupChatRequest $request)
    {
        $group_chat = GroupChat::create([
            'name' => $request->group_chat_name
        ] + $request->safe()->only('user_id'));

        if ($request->hasFile('image_file')) {
            ImageService::uploadGroupChatImage([
                'model_id' => $group_chat->id,
                'model_type' => 'App\\Models\\GroupChat',
                'image' => $request->file('image_file')
            ]);
        }


        return $this->sendResponse([], 'You have successfully created a group chat.', 201);
    }

    public function showGroupChatList()
    {
        $group_chat_list = GroupChat::with([
                'latest_group_message' => fn($latest_group_message) => $latest_group_message->select(['id', 'sender_id', 'message_body', 'group_chat_id', 'synced_users', 'uuid', 'created_at']),
                'group_chat_users' => fn($group_chat_users) => $group_chat_users->select(['id', 'first_name', 'last_name', 'suffix'])
            ])
            ->withCount('group_chat_users')
            ->whereHas('group_chat_users', function (Builder $query) {
                $query->select(['id', 'first_name', 'last_name', 'suffix'])->where('user_id', $this->authUser()->id);
            })
            ->orWhere('user_id', auth()->guard('api')->user()->id)
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return $this->sendResponse($group_chat_list, 'List of all group chats.', 200);
    }

    public function getContacts($group_chat_uuid) {
        $group_chat = GroupChat::where('uuid', $group_chat_uuid)->with('group_chat_users')->first();

        if (!$group_chat) {
            return $this->sendResponse([], 'No group chat found.', 404);
        }
        
        $searchParts = explode(' ', request('search'));
        
        $contacts = User::select(['id', 'first_name', 'last_name', 'suffix', 'role', 'email'])
            ->whereNotIn('id', $group_chat->group_chat_users->pluck('id'))
            ->when(!empty(request('search')), function ($query) use ($searchParts) {
                Helper::queryNameSearch($searchParts, $query);
            })->paginate(request('paginate'));

        return $this->sendResponse($contacts, 'List contacts not in group', 200);

    }

    public function showGroupChat($group_chat_uuid)
    {
        $group_chat = GroupChat::with([
            'group_messages' => function($group_messages) {
                return $group_messages
                    ->with(['sender' => fn($sender) => $sender->select(['id', 'first_name', 'last_name', 'suffix'])])
                    ->latest()
                    ->paginate(20);
            }, 
            'group_chat_users' => fn($group_chat_users) => $group_chat_users->select(['id', 'first_name', 'last_name', 'suffix', 'role']),
            'group_chat_has_course_group'
        ])
        ->withCount('group_messages')
        ->where('uuid', $group_chat_uuid)
        ->first();

        if (!$group_chat) {
            return $this->sendResponse([], 'No group chat found.', 404);
        }

        return $this->sendResponse($group_chat, 'Show group chat.', 200);
    }

    public function addUsersToGroupChat(Request $request, $group_chat_uuid)
    {
        $request->validate([
            'user_id.*' => 'required|exists:users,id',
        ]);

        $group_chat = GroupChat::where('uuid', $group_chat_uuid)->first();

        if (!$group_chat) {
            return $this->sendResponse([], 'No group chat found.', 404);
        }

        $group_chat->group_chat_users()->syncWithoutDetaching($request->user_id);

        $users = User::whereIn('id', $request->user_id)->get();

        foreach ($users as $user) {
            $group_message = $group_chat->group_messages()->create([
                'group_chat_id' => $group_chat->id,
                'message_body' => $user->full_name . ' has been added to the group.',
                'synced_users' => true,
                'sender_id' => $this->authUser()->id
            ]);

            $data = [
                'name' => $this->authUser()->full_name,
                'group_name' => $group_chat->name,
                'group_chat_uuid' => $group_chat->uuid,
                'synced_users' => true,
                'action' => 'added'
            ];

            Notification::send($user, new GroupMessageNotification($data));
        }

        $group_chat->touch();
        
        return $this->sendResponse([], 'Users have been added.', 201);
    }

    public function updateGroupPhoto(UpdateGroupChatPhotoRequest $request, $group_chat_uuid)
    {
        $group_chat = GroupChat::where('uuid', $group_chat_uuid)->first();

        if (!$group_chat) {
            return $this->sendResponse([], 'No group chat found.', 404);
        }

            ImageService::uploadGroupChatImage([
                'model_id' => $group_chat->id,
                'model_type' => 'App\\Models\\GroupChat',
                'image' => $request->file('image_file')
            ]);
        

        return $this->sendResponse([], 'You have successfully updated group chat photo', 201);
        
    }

    public function removeGroupPhoto($group_chat_uuid)
    {
        $group_chat = GroupChat::where('uuid', $group_chat_uuid)->first();

        if (!$group_chat) {
            return $this->sendResponse([], 'No group chat found.', 404);
        }

        $group_chat->image()->delete();

        return $this->sendResponse([], 'You have successfully removed group chat photo', 201);
        
    }

    public function sendGroupMessage(GroupChatMessageRequest $request)
    {
        $request['synced_users'] = false;
        $group_message = GroupMessage::create($request->validated());
        $group_chat = GroupChat::where('id', $request->group_chat_id)->first();
        $group_chat->touch();
        $group_chat_creator = User::where('id', $group_chat->user_id)->get();
        $group_chat_users = $group_message->group_chat->group_chat_users;

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file_attachment) {
                AttachmentService::uploadGroupFileAttachment([
                    'model_id' => $group_message->id,
                    'model_type' => 'App\\Models\\GroupMessage',
                    'group_chat_uuid' => $group_chat->uuid,
                    'group_message_uuid' => $group_message->uuid,
                    'file' => $file_attachment
                ]);
            }
        }

        $data = [
            'name' => $this->authUser()->full_name,
            'group_name' => $group_message->group_chat->name,
            'group_chat_uuid' => $group_message->group_chat->uuid,
            'synced_users' => false,
            'action' => ''
        ];

        Notification::send($group_chat_users->except($request->sender_id), new GroupMessageNotification($data));

        // if ($group_chat->user_id === $this->authUser()->id) {
        //     Notification::send($group_chat_users->except($this->authUser()->id), new GroupMessageNotification($data));
        // } else {
        //     Notification::send($group_chat_users->except($this->authUser()->id), new GroupMessageNotification($data));
        // }

        return $this->sendResponse([], 'Message sent.', 201);
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
