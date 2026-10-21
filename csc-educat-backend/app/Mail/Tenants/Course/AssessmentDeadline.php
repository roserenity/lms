<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssessmentDeadline extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Assessment Deadline',
        );
    }

    public function build()
    {
        $tenant_id = $this->data['tenant_id'];

        $view = 'emails.tenants.default.course.course_assessment_deadline';

        return $this->view($view)
            ->with([
                'name' => $this->data['name'],
                'course_title' => $this->data['course_title'],
                'assessment_title' => $this->data['assessment_title'],
                'assessment_due_date' => $this->data['assessment_due_date'],
                // 'current_progress' => $this->data['current_progress'],
                'url' => $this->data['url']
            ]);
    }
}
