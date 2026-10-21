<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CourseGroup;
use App\Models\GroupChat;
use Illuminate\Http\Request;

use App\Helpers\Helper;
use App\Notifications\GroupMessageNotification;
use App\Services\Tenants\ImageService;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Notification;
class CourseGroupController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth.instructor');
    }

    public function index()
    {

        $course_groups = CourseGroup::withCount(['course_group_has_users'])
            ->when(request('search'), function ($query) {
                $query->where('name', 'LIKE', '%' . request('search') . '%');
            })->when(request('course_id'), function ($query) {
                $query->where('course_id',  request('course_id'));
            })
            ->with(['course_group_has_users', 'course_group_has_group_chat', 'course' => fn($query) => $query->select(['id', 'title'])])->paginate(request('paginate'));

        return $this->sendResponse($course_groups, 'Course group list', 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => [
                'required',
                'string',
                Rule::unique('course_groups')->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id);
                }),
            ],
            'sub_groups' => 'sometimes|array'
        ]);

        $course_group = CourseGroup::create(array_merge(
            $request->all(),
            ['user_id' => $this->authUser()->id]
        ));
        
        $group_chat = GroupChat::create([
            'name' => $request->name,
            'user_id' => $this->authUser()->id
        ]);

        $group_chat->group_chat_has_course()->attach($request->course_id);
        $group_chat->group_chat_users()->attach($this->authUser()->id);

        $course_group->course_group_has_group_chat()->attach($group_chat->id);
        $course_group->course_group_has_users()->attach($this->authUser()->id);

        return $this->sendResponse($course_group, 'User group created', 201);
    }

    public function show($id) 
    {
        $course_group = CourseGroup::with(['course_group_has_group_chat' => fn($query) => $query->limit(1)])->findOrFail($id);

        return $this->sendResponse($course_group, 'User group', 200);
    }

    public function update($id, Request $request) 
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('course_groups')->where(function ($query) use ($request) {
                    return $query->where('course_id', $request->course_id);
                }),
            ],
        ]);

        $course_group = CourseGroup::findOrFail($id);

        $course_group->update(['name' => $request->name]);

        return $this->sendResponse([], 'You have successfully updated a user group.', 200);
    }

    public function destroy($id)
    {
        $course_group = CourseGroup::findOrFail($id);
        
        foreach($course_group->course_group_has_group_chat as $groupchat) {
            $groupchat->delete();
        }
        $course_group->course_group_has_group_chat()->detach();
        $course_group->delete();

        return $this->sendResponse([], 'You have deleted this user group', 200);
    }

    public function getCourseGroupMembers($id, Request $request)
    {

        $searchParts = explode(' ', request('search'));

        $course_group_members = User::whereHas('course_groups', function ($query) use ($id) {
                $query->where('course_group_has_users.course_group_id', $id);
            })
            ->when(!empty(request('search')), function ($query) use ($searchParts) {
                Helper::queryNameEmailSearch($searchParts, $query);
            })->paginate($request->paginate);

        return $this->sendResponse($course_group_members, 'User group members', 200);
    }

    public function createGroupChat($course_group_id, Request $request)
    {

        $course_group = CourseGroup::with('course_group_has_users')->findOrFail($course_group_id);

        $group_chat = GroupChat::create([
            'name' => $request->group_chat_name,
            'user_id' => $this->authUser()->id
        ]);

        if ($request->hasFile('image_file')) {
            ImageService::uploadGroupChatImage([
                'model_id' => $group_chat->id,
                'model_type' => 'App\\Models\\GroupChat',
                'image' => $request->file('image_file')
            ]);
        }

        $course_group->course_group_has_group_chat()->syncWithoutDetaching($group_chat->id);

        $course_group->save();

        $users = $course_group->course_group_has_users()->pluck('user_id')->toArray();
        
        $users[] = $this->authUser()->id;
        $users = array_unique($users); // Ensure no duplicates

        // Sync all users in a single call
        $group_chat->group_chat_users()->syncWithoutDetaching($users);

        // Notify users
        $users = User::whereIn('id', $users)->get();

        foreach ($users as $user) {
            $group_chat->group_messages()->create([
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

        return $this->sendResponse($group_chat, 'You have successfully created a group chat.', 201);

    }
    
}
