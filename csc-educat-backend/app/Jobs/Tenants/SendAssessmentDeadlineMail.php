<?php

namespace App\Jobs\Tenants;

use App\Services\Tenants\ResendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Log;

class SendAssessmentDeadlineMail implements ShouldQueue
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
        try {
            ResendService::send($this->data['email'], 'Course Assessment Deadline', $this->buildHtmlBody());
        } catch (\Exception $e) {
            
            Log::error("Learner Assessment Deadline resend error: " . $e);

        } 
    }

    protected function buildHtmlBody()
    {
        return view('emails.tenants.default.course.course_assessment_deadline', [
                'name' => $this->data['name'],
                'course_title' => $this->data['course_title'],
                'assessment_title' => $this->data['assessment_title'],
                'assessment_due_date' => $this->data['assessment_due_date'],
                // 'current_progress' => $this->data['current_progress'],
                'url' => $this->data['url']
        ])->render();
    }
}
