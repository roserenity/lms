<?php

namespace App\Observers;

use App\Models\QuestionPool;

use Str;

class QuestionPoolObserver
{
    //
    public function creating(QuestionPool $questionPool)
    {
      $questionPool->uuid = Str::uuid();
    }

}
