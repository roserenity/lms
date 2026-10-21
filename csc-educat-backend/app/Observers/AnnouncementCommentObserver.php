<?php

namespace App\Observers;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;


use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\AnnouncementCommentNotification;

use Str;

class AnnouncementCommentObserver
{
    public function creating(AnnouncementComment $announcementComment)
    {
        $announcementComment->uuid = Str::uuid();
    }

    /**
     * Handle the AnnouncementComment "created" event.
     *
     * @param  \App\Models\AnnouncementComment  $announcementComment
     * @return void
     */
    public function created(AnnouncementComment $announcementComment)
    {
        $course = $announcementComment->commentable->course;

        $data = [
            'name' => auth()->guard('api')->user()->full_name,
            'photo' => auth()->guard('api')->user()->image->url ?? null,
            'course' => $course->title,
            'course_uuid' => $course->uuid,
            'announcement_id' => $announcementComment->commentable->id,
            'announcement_comment_id' => $announcementComment->id
        ];

        // if (auth()->guard('api')->user()->role === User::ROLE_INSTRUCTOR) {
        //     $enrolled_users = Enrollment::where('course_id', $course->id)->pluck('user_id');

        //     $users = User::whereIn('id', $enrolled_users)->get();
        //     Notification::send($users, new AnnouncementCommentNotification($data));
        // } else {
            $parent_comment =  AnnouncementComment::where('id', $announcementComment->parent_id)->first();

            if($parent_comment === null) {
                $announcement_comment = Announcement::where('id', $announcementComment->commentable_id)->first();
                if($announcement_comment->user_id != auth()->guard('api')->user()->id) {
                    $receiver = User::find($announcement_comment->user_id);
                    Notification::send($receiver, new AnnouncementCommentNotification($data));
                }
            }else if($parent_comment->user_id !== auth()->guard('api')->user()->id) {
                $comments = AnnouncementComment::where('user_id', $parent_comment->user_id)->pluck('id');
                if ($comments->contains($announcementComment->parent_id)) {
                    $receiver = User::find($parent_comment->user_id);
                    Notification::send($receiver, new AnnouncementCommentNotification($data));
                }
            }
        //}
    }

    /**
     * Handle the AnnouncementComment "updated" event.
     *
     * @param  \App\Models\AnnouncementComment  $announcementComment
     * @return void
     */
    public function updated(AnnouncementComment $announcementComment)
    {
        //
    }

    /**
     * Handle the AnnouncementComment "deleted" event.
     *
     * @param  \App\Models\AnnouncementComment  $announcementComment
     * @return void
     */
    public function deleted(AnnouncementComment $announcementComment)
    {
        //
    }

    /**
     * Handle the AnnouncementComment "restored" event.
     *
     * @param  \App\Models\AnnouncementComment  $announcementComment
     * @return void
     */
    public function restored(AnnouncementComment $announcementComment)
    {
        //
    }

    /**
     * Handle the AnnouncementComment "force deleted" event.
     *
     * @param  \App\Models\AnnouncementComment  $announcementComment
     * @return void
     */
    public function forceDeleted(AnnouncementComment $announcementComment)
    {
        //
    }
}
