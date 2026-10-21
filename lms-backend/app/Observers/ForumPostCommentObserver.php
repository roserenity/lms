<?php

namespace App\Observers;

use App\Models\ForumPost;
use App\Models\ForumPostComment;
use App\Models\User;
use App\Notifications\ForumPostCommentNotification;
use Str;

use Illuminate\Support\Facades\Notification;

class ForumPostCommentObserver
{
    public function creating(ForumPostComment $forum_post_comment)
    {
        $forum_post_comment->uuid = Str::uuid();
    }

    /**
     * Handle the ForumPostComment "created" event.
     *
     * @param  \App\Models\ForumPostComment  $forum_post_comment
     * @return void
     */
    public function created(ForumPostComment $forum_post_comment)
    {
        $course = $forum_post_comment->commentable->course;

        $post = ForumPost::find($forum_post_comment->commentable_id);

        $data = [
            'name' => auth()->guard('api')->user()->full_name,
            'photo' => auth()->guard('api')->user()->image->url ?? null,
            'title' => $post->title ?? null,
            'course' => $course->title ?? null,
            'course_uuid' => $course->uuid ?? null,
            'forum_post_id' => $forum_post_comment->commentable->id,
            'forum_post_comment_id' => $forum_post_comment->id,
            'parent_id' => $forum_post_comment->parent_id
        ];

        // if (auth()->guard('api')->user()->role === User::ROLE_INSTRUCTOR) {
        //     $enrolled_users = Enrollment::where('course_id', $course->id)->pluck('user_id');

        //     $users = User::whereIn('id', $enrolled_users)->get();
        //     Notification::send($users, new ForumPostCommentNotification($data));
        // } else {
            $parent_comment =  ForumPostComment::where('id', $forum_post_comment->parent_id)->first();

            if($parent_comment === null) {
                $forum_post_comment = ForumPost::where('id', $forum_post_comment->commentable_id)->first();
                if($forum_post_comment->user_id != auth()->guard('api')->user()->id) {
                    $receiver = User::find($forum_post_comment->user_id);
                    Notification::send($receiver, new ForumPostCommentNotification($data));
                }
            }else if($parent_comment->user_id !== auth()->guard('api')->user()->id) {
                $comments = ForumPostComment::where('user_id', $parent_comment->user_id)->pluck('id');
                if ($comments->contains($forum_post_comment->parent_id)) {
                    $receiver = User::find($parent_comment->user_id);
                    Notification::send($receiver, new ForumPostCommentNotification($data));
                }
            }
        //}
    }

    /**
     * Handle the ForumPostComment "updated" event.
     *
     * @param  \App\Models\ForumPostComment  $forum_post_comment
     * @return void
     */
    public function updated(ForumPostComment $forum_post_comment)
    {
        //
    }

    /**
     * Handle the ForumPostComment "deleted" event.
     *
     * @param  \App\Models\ForumPostComment  $forum_post_comment
     * @return void
     */
    public function deleted(ForumPostComment $forum_post_comment)
    {
        //
    }

    /**
     * Handle the ForumPostComment "restored" event.
     *
     * @param  \App\Models\ForumPostComment  $forum_post_comment
     * @return void
     */
    public function restored(ForumPostComment $forum_post_comment)
    {
        //
    }

    /**
     * Handle the ForumPostComment "force deleted" event.
     *
     * @param  \App\Models\ForumPostComment  $forum_post_comment
     * @return void
     */
    public function forceDeleted(ForumPostComment $forum_post_comment)
    {
        //
    }
}
