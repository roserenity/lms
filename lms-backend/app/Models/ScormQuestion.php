<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScormQuestion extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'scorm_activity_id',
    'question_uuid',
    'question',
    'type_of_question',
    'choices',
    'correct_answers',
  ];

  protected $hidden = [
    'deleted_at'
  ];

  protected $casts = [
    'choices' => 'array',
    'correct_answers' => 'array',
  ];

  public function scorm_activity()
  {
    return $this->belongsTo(ScormActivity::class, 'scorm_activity_id');
  }

  public function scorm_activity_answers()
  {
    return $this->hasMany(ScormActivityAnswer::class, 'question_id');
  }
}