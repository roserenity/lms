<?php

namespace App\Jobs\Tenants;

use App\Services\Tenants\ResendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendManualCourseEnrollmentApproveEmail implements ShouldQueue
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
        ResendService::send($this->data['email'], 'Manual Course Enrollment Approve', $this->buildHtmlBody());
    }

    /**
     * Build the HTML content for the email.
     *
     * @return string
     */
    protected function buildHtmlBody()
    {
        return view('emails.tenants.default.course.manual_course_enrollment_approve', [
            'name' => $this->data['name'],
            'course_title' => $this->data['course_title'],
            'course_description' => $this->data['course_description'],
            'course_image' => $this->data['course_image'],
            'url' => $this->data['url'] . '/course/' . $this->data['course_uuid']
        ])->render();
    }

}
