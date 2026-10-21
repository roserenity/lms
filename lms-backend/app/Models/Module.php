<?php

namespace App\Models;

use Illuminate\Support\Arr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Carbon;

use Log;

class Module extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'course_id',
    'title',
    'description',
    'validity',
    'available_from',
    'available_to',
    'sort_no',
    'uploader_id',
    'contact_person',
    'version',
    'language',
    'data'
  ];

  protected $appends = [
    'module_status',
    'module_is_locked', 
    'module_topic_is_done',
    'module_group_activity_is_done',
    'module_subtopic_is_done',
    'module_assessment_is_done',
    'module_evaluation_is_done'];

  protected $casts = [
    'created_at' => 'date:Y-m-d h:i:s',
    'updated_at' => 'date:Y-m-d h:i:s',
    'language' => 'array',
    'data' => 'array'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  public function assessments()
  {
    return $this->hasMany(Assessment::class);
  }

  public function group_activities()
  {
    return $this->hasMany(GroupActivity::class);
  }

  public function topics()
  {
    return $this->hasMany(Topic::class)->orderBy('sort_no', 'ASC')->orderBy('id', 'DESC');
  }

  public function prerequisites()
  {
    return $this->hasMany(ModulePrerequisite::class);
  }

  public function assessment_score()
  {
    return $this->hasManyThrough(AssessmentQuestion::class, Assessment::class);
  }

  public function my_assessment_score()
  {
    return $this->hasManyThrough(AssessmentScore::class, Assessment::class);
  }

  public function module_evaluations()
  {
    return $this->hasMany(ModuleEvaluation::class);
  }

  public function getModuleStatusAttribute()
  {
    $module_prerequisite_counts = $this->prerequisites()->count();
    $unlocked_module_counts = 0;
    foreach ($this->prerequisites as $item) {
      if ($item->module_status == 'unlocked') {
        $unlocked_module_counts++;
      }
    }
    return $unlocked_module_counts == $module_prerequisite_counts ? 'unlocked' : 'lock';
  }

  public function getModuleIsLockedAttribute()
  {
      // If validity is anytime, lock, or not set, then the module is not locked
      if (in_array($this->validity, ['anytime', 'lock', null])) {
          return false;
      }

      try {
          $currentDate = Carbon::now('UTC')->startOfDay();
          $availableFromDate = $this->available_from 
              ? Carbon::createFromFormat('Y-m-d', $this->available_from, 'UTC')->startOfDay() 
              : null;
          $availableToDate = $this->available_to 
              ? Carbon::createFromFormat('Y-m-d', $this->available_to, 'UTC')->startOfDay() 
              : null;

          // If neither date is set, don't lock the module
          if (!$availableFromDate && !$availableToDate) {
              return false;
          }

          // Only available_to is set
          if (!$availableFromDate && $availableToDate) {
              return $currentDate->greaterThanOrEqualTo($availableToDate);
          }

          // Only available_from is set
          if ($availableFromDate && !$availableToDate) {
              return $currentDate->lessThanOrEqualTo($availableFromDate);
          }

          // Both dates are set
          if ($currentDate->greaterThanOrEqualTo($availableFromDate) && $currentDate->lessThanOrEqualTo($availableToDate)) {
            return false;
          } else return true;
          
      } catch (\Exception $e) {
          // On error, assume it's not locked
          return false;
      }
  }


  /* public function getModuleIsPrerequisiteAttribute()
  {
    return ModulePrerequisite::where('prerequisite_module_id', $this->id)->exists();
  } */

  public function countProgress($ids, $model)
  {
    $total = StudentModuleProgress::whereIn('model_id', $ids)
      ->where('model_type', $model)
      ->where('user_id', auth()->guard('api')->id())
      ->where('status', StudentModuleProgress::STATUS_DONE)
      ->distinct('model_id')
      ->count();

    return $total;
  }

  public function getModuleTopicIsDoneAttribute()
  {
    $count = $this->topics()->count();
    //if($count === 0)  return false;

    $topics = $this->topics()->pluck('id');

    $done_total = $this->countProgress($topics, 'App\\Models\\Topic');

    return $done_total === $count;
  }

  public function getModuleSubtopicIsDoneAttribute()
  {
    $count = collect($this->topics->pluck('sub_topics')->flatten())->count();

    $subtopics = $this->topics->pluck('sub_topics')->map->pluck('id');

    $done_total = $this->countProgress(Arr::flatten($subtopics), 'App\\Models\\SubTopic');

    return $done_total === $count;
  }

  public function getModuleAssessmentIsDoneAttribute()
  {
    $count = Assessment::where('module_id', $this->id)->count();
    $assessments = Assessment::where('module_id', $this->id)->pluck('id')->toArray();

    $done_total = $this->countProgress(Arr::flatten($assessments), 'App\\Models\\Assessment');

    return $done_total === $count;
  }

  public function getModuleEvaluationIsDoneAttribute()
  {
    $count = $this->module_evaluations()->count();

    $evaluations = $this->module_evaluations->pluck('id');
    
    $done_total = $this->countProgress($evaluations, 'App\\Models\\ModuleEvaluation');

    return $done_total === $count;
  }

  public function getModuleGroupActivityIsDoneAttribute()
  {
    $count = $this->group_activities()->count();

    $evaluations = $this->group_activities->pluck('id');
    
    $done_total = $this->countProgress($evaluations, 'App\\Models\\GroupActivities');

    return $done_total === $count;
  }

  public function scopeSelectAttributes($query)
  {
      return $query->select([
          'id', 
          'uuid', 
          'course_id', 
          'title',
          'validity',
          'available_from',
          'available_to',
          'contact_person',
          'version',
          'language',
          'data'
      ])
      ->orderBy('sort_no', 'ASC');
  }

  public function module_content()
  {
    return $this->hasMany(ModuleContent::class)->with('contentable')->orderBy('sort_no', 'ASC');
  }
  
}
