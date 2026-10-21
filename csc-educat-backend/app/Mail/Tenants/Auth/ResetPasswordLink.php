<?php

namespace App\Mail\Tenants\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;
class ResetPasswordLink extends Mailable implements ShouldQueue
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
        // $view = 'emails.tenants.' . $tenant_id . '.auth.reset_password';

        // if(!View::exists($view)) {
            $view = 'emails.tenants.default.auth.reset_password';
        // }

        return $this->view($view)
            ->with([
                'name' => $this->data['name'],
                'url' => $this->data['url']
            ]);
    }
}
