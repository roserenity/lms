<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubTopic extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'course_id',
    'topic_id',
    'title',
    'sort_no',
    'uploader_id'
  ];

  protected $appends = ['student_done_status'];

  protected $casts = [
    'created_at' => 'date:Y-m-d h:i:s',
    'updated_at' => 'date:Y-m-d h:i:s'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  public function course()
  {
    return $this->belongsTo(Course::class);
  }

  public function topic()
  {
    return $this->belongsTo(Topic::class);
  }

  public function learning_material()
  {
    return $this->morphOne(LearningMaterial::class, 'learnable');
  }

  public function student_sub_topic_progress()
  {
    return $this->morphOne(StudentModuleProgress::class, 'model')->where('user_id', auth()->guard('api')->id());
  }

  public function sub_topic_has_assessments()
  {
    return $this->belongsToMany(Assessment::class, 'assessment_has_sub_topics');
  }

  public function sub_topic_has_group_activities()
  {
    return $this->belongsToMany(GroupActivity::class, 'group_activity_has_sub_topics');
  }

  public function getStudentDoneStatusAttribute()
  {
    return $this->student_sub_topic_progress()->where('status', StudentModuleProgress::STATUS_DONE)->count() > 0 ? true : false;
  }

  public function getStudentSubtopicProgress($user_id)
  {
    return $this->student_sub_topic_progress()->where('user_id', $user_id)->first();
  }

  public function student_module_progress()
  {
    return $this->morphMany(StudentModuleProgress::class, 'model');
  }

  public function getStudentSubTopicStatus($user_id)
  {
    return $this->student_module_progress()->where('status', StudentModuleProgress::STATUS_DONE)->where('user_id', $user_id)->count() > 0 ? true : false;
  }

  public function scorm()
  {
    return $this->morphOne(Scorm::class, 'model');
  }
  
  public function scorm_activities()
  {
    return $this->morphMany(ScormActivity::class, 'model');
  }

  public function scorm_progress()
  {
    return $this->morphMany(ScormStudentProgress::class, 'model');
  }

  public function student_scorm_progress()
  {
    return $this->morphOne(ScormStudentProgress::class, 'model')->where('user_id', auth()->guard('api')->id());
  }
  
}
