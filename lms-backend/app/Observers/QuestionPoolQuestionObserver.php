<?php

namespace App\Observers;

use App\Models\QuestionPoolQuestion;

use Str;

class QuestionPoolQuestionObserver
{
    //
    public function creating(QuestionPoolQuestion $questionPoolQuestion)
    {
      $questionPoolQuestion->uuid = Str::uuid();
    }

}
