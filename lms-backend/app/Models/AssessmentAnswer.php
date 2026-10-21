<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentAnswer extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'uuid',
    'assessment_question_id',
    'correct_answer',
    'choices'
  ];

  protected $casts = [
    'choices' => 'array'
  ];

  protected $hidden = [
    // 'correct_answer',
    'deleted_at'
  ];

  public function assessment_question()
  {
    return $this->belongsTo(AssessmentQuestion::class);
  }
}
