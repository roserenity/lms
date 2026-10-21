<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScormActivityAnswer extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'scorm_activity_id',
    'question_id',
    'user_id',
    'answer',
    'is_correct'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  protected $casts = [
    'answer' => 'array'
  ];

  public function scorm_activity()
  {
    return $this->belongsTo(ScormActivity::class, 'scorm_activity_id');
  }

  public function scorm_question()
  {
    return $this->belongsTo(ScormQuestion::class, 'question_id');
  }

  public function user()
  {
      return $this->belongsTo(User::class, 'user_id');
  }
  
}