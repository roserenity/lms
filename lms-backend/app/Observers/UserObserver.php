<?php

namespace App\Observers;

use App\Jobs\Tenants\SendVerificationEmail;
use App\Mail\Tenants\Auth\VerifyEmail;
use App\Models\User;
use App\Helpers\StandaloneConfig;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

use Log;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function creating(User $user)
    {
        $user->uuid = Str::uuid();
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
        if ($user->isDirty('email')) {

            $creds = implode(':', [$user->uuid, $user->email]);
            $hash = Crypt::encryptString($creds);

            $apiKey = config("services.sender.key");

            if($apiKey) {
                try {
                    SendVerificationEmail::dispatch([
                        'name' => $user->full_name,
                        'url' => StandaloneConfig::domainName() . '/verify-email?' . http_build_query([
                        'hash' => $hash
                        ]),
                        'email' => $user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error('Email Verification Error' . $e );
                }
            } else {
                Mail::to($user->email)->send(new VerifyEmail([
                    'name' => $user->full_name,
                    'url' => StandaloneConfig::domainName() . '/verify-email?' . http_build_query([
                    'hash' => $hash
                    ])
                ]));
            }

        }
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
