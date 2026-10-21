<?php

namespace App\Jobs\Tenants;

use App\Services\Tenants\ResendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCourseInvitationEditorEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ResendService::send($this->data['email'], 'Course Editor Invitation', $this->buildHtmlBody());
    }

    protected function buildHtmlBody()
    {
        $path = '/instructor/my-courses/' . $this->data['course_status'];

        return view('emails.tenants.default.course.course_invitation_editor', [
                'name' => $this->data['name'],
                'course_title' => $this->data['course_title'],
                'course_description' => $this->data['course_description'],
                'course_image' => $this->data['course_image'],
                'url' => $this->data['url'] . $path
        ])->render();
    }
}
