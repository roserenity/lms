<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScormActivityScore extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'course_id',
    'scorm_activity_id',
    'user_id',
    'score',
    'passed'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  public function user()
  {
      return $this->belongsTo(User::class, 'user_id');
  }

  public function scorm_activity()
  {
      return $this->belongsTo(ScormActivity::class, 'scorm_activity_id');
  }

  public function scorm_activity_answers()
  {
    return $this->hasMany(ScormActivityAnswer::class)->where('scorm_activity_id', $this->scorm_activity_id);
  }

  public function my_scorm_activity_answers()
  {
    return $this->hasMany(ScormActivityAnswer::class, 'scorm_activity_id')->where('user_id', auth()->guard('api')->user()->id);
  }
  
}