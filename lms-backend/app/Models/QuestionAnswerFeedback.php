<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionAnswerFeedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question_answer_id',
        'user_id',
        'comment'
    ];

    public function question_answer()
    {
        return $this->belongsTo(QuestionAnswer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
