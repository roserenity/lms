<?php

namespace App\Observers;

use App\Models\User;
use App\Models\ForumPost;
use App\Notifications\ForumPostReviewNotification;
use Illuminate\Support\Facades\Notification;

use Str;

class ForumPostObserver
{
    public function creating(ForumPost $forumpost)
    {
        $forumpost->uuid = Str::uuid();
    }

    public function created(ForumPost $forumpost)
    {
        $poster = User::find($forumpost->id);

        if($poster->role != User::ROLE_USER) {
            $poster->update([ 'status' => ForumPost::IS_APPROVED]);
        }
    }

    public function updated(ForumPost $forumpost)
    {
        if ($forumpost->isDirty('is_pinned') && $forumpost->is_pinned) {
            ForumPost::where('id', '!=', $forumpost->id)
                ->update(['is_pinned' => false]);
        }

        
        if ($forumpost->isDirty('status') && in_array($forumpost->status, [ForumPost::IS_APPROVED, ForumPost::IS_DECLINED, ForumPost::IS_REMOVED])) {
            $receiver = User::find($forumpost->user_id);

            if($receiver && $receiver->role == User::ROLE_USER) {
                $data = [
                    'name' => $receiver->full_name,
                    'title' => $forumpost->title,
                    'status' => $forumpost->status,
                    'forum_post_id' => $forumpost->id,
                    'photo' => auth()->guard('api')->user()->image,
                    'moderator_name' => auth()->guard('api')->user()->full_name,
                    'moderator_email' => auth()->guard('api')->user()->email,
                ];
    
                ForumPost::where('id', '!=', $forumpost->id)
                    ->update(['reviewer_id' => auth()->guard('api')->user()->id]);
    
                if($receiver) {
                    Notification::send($receiver, new ForumPostReviewNotification($data));
                }
            }
        }
    }

}
