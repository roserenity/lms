<?php

namespace App\Jobs\Tenants;

use App\Services\Tenants\ResendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCommitmentLetterConfimationEmail implements ShouldQueue
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
        ResendService::send($this->data['email'], 'Acknowledgement Receipt for Commitment Letter [ DO NOT REPLY ]', $this->buildHtmlBody());
    }

    /**
     * Build the HTML content for the email.
     *
     * @return string
     */
    protected function buildHtmlBody()
    {
        return view('emails.tenants.default.payment.commitment_letter', [
            'name' => $this->data['name'],
            'course_title' => $this->data['course_title'],
        ])->render();
    }
}
