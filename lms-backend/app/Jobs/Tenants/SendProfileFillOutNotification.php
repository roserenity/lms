<?php

namespace App\Jobs\Tenants;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;
use App\Notifications\ProfileFilloutNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendProfileFillOutNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900; // 5 minutes
    public $tries = 5;

    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function handle()
    {
        try {
            $customRegistrationPages = CustomRegistrationPage::all()->keyBy('id');
            $fieldsByPage = CustomRegistrationField::all()->groupBy('custom_registration_page_id');

            User::chunk(100, function ($users) use ($customRegistrationPages, $fieldsByPage) {
                foreach ($users as $user) {
                    $userData = is_array($user->data) ? $user->data : json_decode($user->data, true) ?? [];

                    $needsNotification = false;

                    foreach ($customRegistrationPages as $pageId => $page) {
                        $fields = $fieldsByPage->get($pageId) ?? collect();

                        foreach ($fields as $field) {
                            if (
                                empty($userData) ||
                                !isset($userData[$page->title][$field->input_field_name])
                            ) {
                                $needsNotification = true;
                                Log::info("User ID {$user->id} missing field '{$field->input_field_name}' on page '{$page->title}'");
                                break 2; // break both foreach loops
                            }
                        }
                    }

                    if ($needsNotification) {
                        Log::info("Sending ProfileFilloutNotification to user ID: {$user->id}");

                        $user->notify(new ProfileFilloutNotification([
                            'text' => 'Please take a moment to review and ensure your profile information is up to date.',
                        ]));
                    }
                }
            });

            Log::info("Profile fill-out notification job completed for tenant ID {$this->tenantId}.");
        } catch (\Exception $e) {
            Log::error("Error sending profile fill-out notifications for tenant ID {$this->tenantId}: " . $e->getMessage());
        }
    }
}
