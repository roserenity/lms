<?php

namespace App\Observers;

use App\Models\Advisory;
use App\Models\User;

class AdvisoryObserver
{
    //

    public function created(Advisory $advisory)
    {

        $user = User::find($advisory->uploader_id);

        activity('advisory_update')
            ->causedBy($user)
            ->withProperties([
                'user_id' => $user->id,
                'email' => $user->email,
                'advisory_id' => $advisory->id
            ])
            ->event('created')
            ->performedOn($advisory)
            ->log('Advisory created');

    }

    public function deleted(Advisory $advisory)
    {
        $user = User::find($advisory->uploader_id);

        activity('advisory_update')
            ->causedBy($user)
            ->withProperties([
                'user_id' => $user->id,
                'email' => $user->email,
                'advisory_id' => $advisory->id
            ])
            ->event('deleted')
            ->performedOn($advisory)
            ->log('Advisory deleted');
    }
}
