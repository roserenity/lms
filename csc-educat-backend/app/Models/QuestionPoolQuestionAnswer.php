<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionPoolQuestionAnswer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question_pool_question_id',
        'correct_answer',
        'choices',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    protected $casts = [
        'choices' => 'array',
    ];
}
