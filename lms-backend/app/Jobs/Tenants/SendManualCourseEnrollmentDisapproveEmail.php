<?php

namespace App\Jobs\Tenants;

use App\Models\Course;
use App\Services\Tenants\ResendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendManualCourseEnrollmentDisapproveEmail implements ShouldQueue
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
        ResendService::send($this->data['email'], 'Manual Course Enrollment Disapprove', $this->buildHtmlBody());
    }

        /**
     * Build the HTML content for the email.
     *
     * @return string
     */
    protected function buildHtmlBody()
    {
        if($this->data['type_of_learning'] === Course::IS_BLENDED) {
            return view('emails.tenants.default.course.manual_course_blended_enrollment_disapprove', [
                'name' => $this->data['name'],
                'course_title' => $this->data['course_title'],
                'date_conducted_str' => $this->data['date_conducted_str']
            ])->render();
        } else {
            return view('emails.tenants.default.course.manual_course_elearning_enrollment_disapprove', [
                'name' => $this->data['name'],
                'course_title' => $this->data['course_title'],
                'date_conducted_str' => $this->data['date_conducted_str']
            ])->render();
        }
    }

}
