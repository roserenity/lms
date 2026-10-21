<?php

namespace App\Observers;

use App\Models\Certificate;
use App\Models\User;
use Str;

class CertificateObserver
{
    public function creating(Certificate $certificate)
    {
      $certificate->uuid = Str::uuid();
    }

    /**
     * Handle the Certificate "created" event.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return void
     */
    public function created(Certificate $certificate)
    {
        $user = User::find($certificate->user_id);

        activity('user_certificate_gen')
            ->causedBy($user)
            ->withProperties([
                'user_id' => $certificate->user_id,
                'email' => $user->email,
                'course_id' => $certificate->course_id,
            ])
            ->event('created')
            ->performedOn($user)
            ->log('User Certificate Generated');
    }

    /**
     * Handle the Certificate "updated" event.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return void
     */
    public function updated(Certificate $certificate)
    {
        //
    }

    /**
     * Handle the Certificate "deleted" event.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return void
     */
    public function deleted(Certificate $certificate)
    {
        //
    }

    /**
     * Handle the Certificate "restored" event.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return void
     */
    public function restored(Certificate $certificate)
    {
        //
    }

    /**
     * Handle the Certificate "force deleted" event.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return void
     */
    public function forceDeleted(Certificate $certificate)
    {
        //
    }
}
