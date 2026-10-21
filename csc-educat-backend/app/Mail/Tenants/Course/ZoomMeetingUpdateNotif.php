<?php

namespace App\Mail\Tenants\Course;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class ZoomMeetingUpdateNotif extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $tenant_id = $this->data['tenant_id'];

        // $view = 'emails.tenants.' . $tenant_id . '.course.manual_course_enrollment_decline';

        // if(!View::exists($view)) {
        // }
        
        $view = 'emails.tenants.default.course.course_zoom_meeting_update';

        return $this->view($view)
            ->subject("Course Meeting Update")
            ->with([
                'name' => $this->data['name'],
                'course_title' => $this->data['course_title'],
                'topic' => $this->data['topic'],
                'agenda' => $this->data['agenda'],
                'start_date' => Carbon::parse($this->data['start_date'])->toDateString(),
                'start_time' => Carbon::createFromTimeString($this->data['start_time'])->format('g:i A'),
                'join_url' => $this->data['join_url'],
            ]);
    }
}
