<?php

namespace App\Mail\Central;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ErrorNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $errorDetails;

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
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.central.error_notification')
            ->with([
                'errorDetails' => $this->errorDetails
            ]);
    }
}
