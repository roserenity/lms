<?php

namespace App\Mail\Tenants\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

use App\Models\User;

class SuccessfulRegistration extends Mailable implements ShouldQueue
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
        $tenant_id = $this->data['tenant_id'];

        if ($this->data['user_role'] === User::ROLE_USER) {
            // $view = 'emails.tenants.' . $tenant_id . '.auth.successful_registration_user';
            // if(!View::exists($view)) {
                $view = 'emails.tenants.default.auth.successful_registration_user';
            // }
        } else {
            // $view = 'emails.tenants.' . $tenant_id . '.auth.successful_registration_instructor';
            // if(!View::exists($view)) {
                $view = 'emails.tenants.default.auth.successful_registration_instructor';
            // }
        }

        return $this->view($view)
            ->with([
                'name' => $this->data['name'],
                'email' => $this->data['email'],
                'password' => $this->data['password'],
                'courses' => $this->data['courses'],
                'url' => $this->data['url']
            ]);
    }
}
