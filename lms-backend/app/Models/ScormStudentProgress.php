<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScormStudentProgress extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'course_id',
    'model_id',
    'model_type',
    'user_id',
    'scorm_course_progress',
  ];

  protected $hidden = [
    'deleted_at'
  ];

  public function user()
  {
      return $this->belongsTo(User::class, 'user_id');
  }

  public function model()
  {
      return $this->morphTo();
  }
  
}