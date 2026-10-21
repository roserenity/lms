<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'course_id',
    'module_id',
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

  public function module()
  {
    return $this->belongsTo(Module::class);
  }

  public function learning_material()
  {
    return $this->morphOne(LearningMaterial::class, 'learnable');
  }

  public function sub_topics()
  {
    return $this->hasMany(SubTopic::class);
  }

  public function group_activities()
  {
    return $this->hasMany(GroupActivity::class);
  }

  public function student_topic_progress()
  {
    return $this->morphOne(StudentModuleProgress::class, 'model')->where('user_id', auth()->guard('api')->id());
  }

  public function topic_has_assessments()
  {
    return $this->belongsToMany(Assessment::class, 'assessment_has_topics');
  }
  
  public function topic_has_group_activities()
  {
    return $this->belongsToMany(GroupActivity::class, 'group_activity_has_topics');
  }

  public function getStudentDoneStatusAttribute()
  {
    return $this->student_topic_progress()->where('status', StudentModuleProgress::STATUS_DONE)->count() > 0 ? true : false;
  }

  public function student_module_progress()
  {
    return $this->morphMany(StudentModuleProgress::class, 'model');
  }

  public function getStudentTopicStatus($user_id)
  {
    return $this->morphOne(StudentModuleProgress::class, 'model')->where('status', StudentModuleProgress::STATUS_DONE)->where('user_id', $user_id)->count() > 0 ? true : false;
  }

  public function getStudentTopicProgress($user_id)
  {
    return $this->student_module_progress()->where('user_id', $user_id)->first();
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
  
  public function module_content()
  {
      return $this->morphOne(ModuleContent::class, 'contentable');
  }

}
