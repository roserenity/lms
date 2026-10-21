<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\GroupActivity;
use App\Models\GroupActivityGroupMember;
use App\Models\User;
use App\Services\Tenants\GoogleDriveService;
use Str;

use Log;

class GroupActivityObserver
{
    public function creating(GroupActivity $group_activity)
    {
        $group_activity->uuid = Str::uuid();
    }

    public function created(GroupActivity $group_activity)
    {

    }
    
    public function updated(GroupActivity $group_activity)
    {
    }


}
