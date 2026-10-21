<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentQuestion extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'uuid',
    'assessment_id',
    'question',
    'type_of_question',
    'score',
    'require_file_attachment',
    'require_support_answer',
    'rubric_id',
    'sort_no'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  public function assessment()
  {
    return $this->belongsTo(Assessment::class);
  }

  public function assessment_answer()
  {
    return $this->hasOne(AssessmentAnswer::class);
  }

  public function question_answer()
  {
    return $this->hasOne(QuestionAnswer::class);
  }
}