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

class SendCertificateOfCompletionMail implements ShouldQueue
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
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $attachment = [
            'path' => $this->data['file_url'],
            'filename' => $this->data['certificate_uuid'] . '.pdf'
        ];

        // $attachment = [];

        try {
            ResendService::send($this->data['email'], 'Certificate Of Completion', '<p>Congratulations on successfully completing your training/course! Attached to this email is your Certificate of Completion.</p>', '',  $attachment);
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    /**
     * Build the HTML content for the email.
     *
     * @return string
     */
    protected function buildHtmlBody()
    {
        return view('emails.tenants.default.certificate.certificate_of_completion_'.$this->data['type_of_learning'], [
            'name' => $this->data['name'],
            'course_title' => $this->data['course_title'],
            'date_conducted' => $this->data['date_conducted'] ?? NULL,
            'created_at' => $this->data['created_at'],
            'issue_date' => $this->data['issue_date'],
            'training_hours' => $this->data['training_hours'],
            'qrcode' => $this->data['qrcode'],
            'program' => $this->data['program'],
            'qrcodePDF' => $this->data['qrcodePDF'],
            'logo' => $this->data['logo'],
            'attestor_name' => $this->data['attestor_name'],
            'attestor_designation' => $this->data['attestor_designation'],
            'attestor_info' => $this->data['attestor_info'],
            'attestor_esign' => $this->data['attestor_esign'],
            'signatory_name' => $this->data['signatory_name'],
            'signatory_designation' => $this->data['signatory_designation'],
            'signatory_info' => $this->data['signatory_info'],
            'signatory_esign' => $this->data['signatory_esign'],
        ])->render();
    }
}
