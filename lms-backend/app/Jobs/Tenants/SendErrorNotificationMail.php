<?php

namespace App\Jobs\Tenants;

use App\Services\Tenants\ResendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendErrorNotificationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $errorDetails;
    public $tries = 1;
    public $maxExceptions = 1;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($errorDetails)
    {
        $this->errorDetails = $errorDetails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ResendService::send($this->errorDetails['receiver'], 'LMS Error Notification', $this->buildHtmlBody());
    }

    protected function buildHtmlBody()
    {
        return view('emails.central.error_notification', [
            'errorDetails' => $this->errorDetails
        ])->render();
    }
}
