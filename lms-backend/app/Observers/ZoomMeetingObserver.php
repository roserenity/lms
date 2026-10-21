<?php

namespace App\Observers;

use App\Jobs\Tenants\SendZoomMeetingUpdateMail;
use App\Mail\Tenants\Course\ZoomMeetingUpdateNotif;
use App\Models\User;
use App\Models\ZoomMeeting;

use Log;
use Mail;

class ZoomMeetingObserver
{
    public function updated(ZoomMeeting $zoom_meeting)
    { 
        $users = User::whereHas('enrollments', function($query) use ($zoom_meeting){
            $query->where('course_id', $zoom_meeting->course_id);
        })->get();

        $meeting = ZoomMeeting::where('id', $zoom_meeting->id)->with('course')->first();

        foreach($users as $user) {

            $data = [
                'email' => $user->email,
                'name' => $user->full_name,
                'course_title' => $meeting->course->title,
                'topic' => $meeting->data['topic'],
                'agenda' => $meeting->data['agenda'],
                'start_date' => $meeting->data['start_date'],
                'start_time' => $meeting->data['start_time'],
                'join_url' => $meeting->data['join_url'],
            ];

            
            $apiKey = config("services.sender.key");

            if($apiKey) {
                try {
                    SendZoomMeetingUpdateMail::dispatch($data);
                } catch (\Exception $e) {
                    Log::error('Email sending error: ' . $e);
                }
            } else {
                Mail::to($user->email)->send(new ZoomMeetingUpdateNotif($data));
            }

        }
    }
}
