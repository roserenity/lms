<?php

namespace App\Mail\Tenants\Certificate;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

class CertificateOfCompletion extends Mailable implements ShouldQueue
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

        // $view = 'emails.tenants.' . $tenant_id . '.certificate.certificate_of_completion';

        // if (!View::exists($view)) {
            $view = 'emails.tenants.default.certificate.certificate_of_completion_'.$this->data['type_of_learning'];
        // }

        return $this->view($view)
            ->with([
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
            ])
            ->attach($this->data['file_url'], [
                'as' => $this->data['certificate_uuid'] . '.pdf',
                'mime' => 'application/pdf'
            ]);
    }
}
