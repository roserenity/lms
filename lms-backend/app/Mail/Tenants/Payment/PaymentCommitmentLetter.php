<?php

namespace App\Mail\Tenants\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;


class PaymentCommitmentLetter extends Mailable implements ShouldQueue
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

        $view = 'emails.tenants.default.payment.commitment_letter';

        return $this->view($view)
            ->subject('Acknowledgement Receipt for Commitment Letter [ DO NOT REPLY ]')
            ->with([
                'name' => $this->data['name'],
                'course_title' => $this->data['course_title'],
            ]);
    }
}
