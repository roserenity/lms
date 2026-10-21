<?php

namespace App\Jobs\Tenants;

use App\Services\Tenants\ResendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendVerificationEmail implements ShouldQueue
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
        ResendService::send($this->data['email'], 'Email Verification', $this->buildHtmlBody());
    }

    /**
     * Build the HTML content for the email.
     *
     * @return string
     */
    protected function buildHtmlBody()
    {
        return view('emails.tenants.default.auth.verify_email', [
            'name' => $this->data['name'],
            'url' => $this->data['url']
        ])->render();
    }
}
