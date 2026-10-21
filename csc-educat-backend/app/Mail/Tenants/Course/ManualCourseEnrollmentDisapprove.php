<?php

namespace App\Mail\Tenants\Course;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

class ManualCourseEnrollmentDisapprove extends Mailable implements ShouldQueue
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
        
        if($this->data['type_of_learning'] === Course::IS_BLENDED) {
            $view = 'emails.tenants.default.course.manual_course_blended_enrollment_disapprove';
        } else {
            $view = 'emails.tenants.default.course.manual_course_elearning_enrollment_disapprove';
        }

        return $this->view($view)
            ->with([
                'name' => $this->data['name'],
                'course_title' => $this->data['course_title'],
                'date_conducted_str' => $this->data['date_conducted_str']
            ]);
    }
}
