<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionAnswer extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'user_id',
    'assessment_id',
    'assessment_question_id',
    'answer',
    'support_answer',
    'file',
    'score'
  ];

  protected $with = ['question_answer_feedback'];

  public function assessment()
  {
    return $this->belongsTo(Assessment::class, 'assessment_id');
  }

  public function assessment_question()
  {
    return $this->belongsTo(AssessmentQuestion::class);
  }

  public function question_answer_feedback()
  {
    return $this->hasOne(QuestionAnswerFeedback::class);
  }
}
