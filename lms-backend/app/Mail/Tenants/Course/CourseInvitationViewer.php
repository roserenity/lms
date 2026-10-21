<?php

namespace App\Mail\Tenants\Course;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

class CourseInvitationViewer extends Mailable implements ShouldQueue
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

        // $view = 'emails.tenants.' . $tenant_id . '.course.course_invitation_viewer';
        $path = '/instructor/my-courses/' . $this->data['course_status'];

        // if(!View::exists($view)) {
            $view = 'emails.tenants.default.course.course_invitation_viewer';
        // }

        return $this->view($view)
            ->with([
                'name' => $this->data['name'],
                'course_title' => $this->data['course_title'],
                'course_description' => $this->data['course_description'],
                'course_image' => $this->data['course_image'],
                'url' => $this->data['url'] . $path
            ]);
    }
}
