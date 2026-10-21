<?php

namespace App\Jobs\Tenants;

use App\Models\User;
use App\Services\Tenants\ResendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSuccessfulRegistrationEmail implements ShouldQueue
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
        ResendService::send($this->data['email'], 'Successful Registration', $this->buildHtmlBody());
    }

    /**
     * Build the HTML content for the email.
     *
     * @return string
     */
    protected function buildHtmlBody()
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

        return view($view, [
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'password' => $this->data['password'],
            'courses' => $this->data['courses'],
            'url' => $this->data['url']
        ])->render();
    }

}
