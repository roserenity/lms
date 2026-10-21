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

class SendUserManagementUserDeletionEmail implements ShouldQueue
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
        ResendService::send($this->data['receiver'], 'User Management User Deletion', $this->buildHtmlBody());
    }

    /**
     * Build the HTML content for the email.
     *
     * @return string
     */
    protected function buildHtmlBody()
    {
        return view('emails.tenants.default.user_management.user_deletion', [
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'id' => $this->data['id'],
            'deleted_by' => $this->data['deleted_by'],
            'full_domain' => $this->data['full_domain'],
            'request_url' => isset($this->data['request_url']) ? $this->data['request_url'] : '',
        ])->render();
    }
    
}
