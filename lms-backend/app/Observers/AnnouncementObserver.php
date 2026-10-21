<?php

namespace App\Observers;

use Illuminate\Support\Facades\Notification;

use App\Models\Announcement;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\AnnouncementNotification;

use Str;

class AnnouncementObserver
{
    public function creating(Announcement $announcement)
    {
        $announcement->uuid = Str::uuid();
    }

    /**
     * Handle the Announcement "created" event.
     *
     * @param  \App\Models\Announcement  $announcement
     * @return void
     */
    public function created(Announcement $announcement)
    {
        $course = $announcement->course;

        $enrolled_users = Enrollment::where('course_id', $course->id)->pluck('user_id');

        $users = User::whereIn('id', $enrolled_users)->get();

        $data = [
            'name' => auth()->guard('api')->user()->full_name,
            'photo' => auth()->guard('api')->user()->image->url ?? null,
            'course' => $course->title,
            'course_uuid' => $course->uuid,
            'announcement_id' => $announcement->id,
        ];

        foreach ($users as $user) {
            Notification::send($user, new AnnouncementNotification($data));
        }

        // check if uploader is admin

        $user = User::where('id', $announcement->user_id)->where('role', User::ROLE_ADMIN)->first();

        $course = Course::where('id', $announcement->course_id)->with(['shared_to_users', 'uploader'])->first();

        if($course) {
            foreach ($course->shared_to_users as $user) {
                Notification::send($user, new AnnouncementNotification($data));
            }

            if($user) {
                Notification::send($course->uploader, new AnnouncementNotification($data));
            }
        }

    }

    /**
     * Handle the Announcement "updated" event.
     *
     * @param  \App\Models\Announcement  $announcement
     * @return void
     */
    public function updated(Announcement $announcement)
    {
        //
    }

    /**
     * Handle the Announcement "deleted" event.
     *
     * @param  \App\Models\Announcement  $announcement
     * @return void
     */
    public function deleted(Announcement $announcement)
    {
        //
    }

    /**
     * Handle the Announcement "restored" event.
     *
     * @param  \App\Models\Announcement  $announcement
     * @return void
     */
    public function restored(Announcement $announcement)
    {
        //
    }

    /**
     * Handle the Announcement "force deleted" event.
     *
     * @param  \App\Models\Announcement  $announcement
     * @return void
     */
    public function forceDeleted(Announcement $announcement)
    {
        //
    }
}
