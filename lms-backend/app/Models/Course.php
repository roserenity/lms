<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Carbon;

use App\Models\StudentModuleProgress;

class Course extends Model
{
  use HasFactory, SoftDeletes;

  const IS_PUBLISH = 'PUBLISH';
  const IS_UNPUBLISH = 'UNPUBLISH';
  const IS_DRAFT = 'DRAFT';
  
  const IS_OWNED = 'OWNED';
  const IS_SHARED = 'SHARED TO ME';

  const IS_ELEARNING = 'E-LEARNING';
  const IS_BLENDED = 'BLENDED';
  const IS_MICROLEARNING = 'MICROLEARNING';

  protected $fillable = [
    'course_code',
    'title',
    'description',
    'status',
    'price',
    'is_free',
    'available_until',
    'training_hours',
    'training_credits',
    'type_of_learning',
    'facilitator',
    'credit_training_hours',
    'uploader_id'
  ];

  protected $appends = [
    'user_is_enrolled',
    'course_is_open_for_registration',
  ];

  protected $casts = [
    // 'created_at' => 'date:Y-m-d h:i:s',
    // 'updated_at' => 'date:Y-m-d h:i:s'
    'available_until' => 'array'
  ];

  protected $hidden = [
    'deleted_at',
    'pivot'
  ];

  protected $with = [
    'course_card', 'image'
  ];

  public function image()
  {
    return $this->morphOne(Image::class, 'imageable');
  }

  public function course_card()
  {
    return $this->hasOne(CourseCardColor::class)->where('user_id', auth()->guard('api')->id());
  }

  public function uploader()
  {
    return $this->belongsTo(User::class);
  }

  public function class_categories()
  {
    return $this->belongsToMany(ClassCategory::class, 'course_has_classes');
  }

  public function modules()
  {
    // if($this->type_of_learning === 'BLENDED') {
    //   return $this->hasMany(Module::class)->whereRaw('0 = 1');
    // } else {
      return $this->hasMany(Module::class)->orderBy('sort_no', 'ASC');
    //}
  }

  public function module_evaluations()
  {
      return $this->hasManyThrough(ModuleEvaluation::class, Module::class);
  }

  public function topics()
  {
    return $this->hasMany(Topic::class)->orderBy('sort_no', 'ASC');
  }

  public function sub_topics()
  {
    return $this->hasMany(SubTopic::class)->orderBy('sort_no', 'ASC');
  }

  public function assessments()
  {
    return $this->hasMany(Assessment::class);
  }

  public function group_activities()
  {
    return $this->hasMany(GroupActivity::class);
  }

  public function announcements()
  {
    return $this->hasMany(Announcement::class);
  }

  public function course_favorite_has_users()
  {
    return $this->belongsToMany(User::class, 'course_favorite_has_users');
  }

  public function zoom_meetings()
  {
    return $this->hasMany(ZoomMeeting::class);
  }

  public function shared_to_users()
  {
    return $this->belongsToMany(User::class, 'share_course_has_users')->withPivot('permission');
  }

  public function my_permission()
  {
    return $this->belongsToMany(User::class, 'share_course_has_users')
      ->where('user_id', auth()->guard('api')->id())
      ->withPivot('permission');
  }

  public function assessment_score()
  {
    return $this->hasManyThrough(AssessmentQuestion::class, Assessment::class);
  }

  public function my_assessment_score()
  {
    return $this->hasManyThrough(AssessmentScore::class, Assessment::class)->where('user_id', auth()->guard('api')->user()->id);
  }

  public function my_course_evaluations(){
    return $this->hasManyThrough(StudentCourseEvaluation::class, CourseEvaluation::class)->where('user_id', auth()->guard('api')->user()->id);
  }

  public function my_module_evaluations(){
    return $this->hasManyThrough(StudentModuleEvaluation::class, ModuleEvaluation::class)->where('user_id', auth()->guard('api')->user()->id);
  }

  public function enrollments()
  {
    return $this->hasMany(Enrollment::class);
  }

  public function course_evaluations()
  {
    return $this->hasMany(CourseEvaluation::class);
  }

  public function cart_items()
  {
    return $this->belongsToMany(CartItem::class, 'cart_items');
  }

  public function invoices()
  {
    return $this->hasMany(Invoice::class);
  }

  public function certificate_template()
  {
    return $this->hasOne(CourseCertificateTemplate::class);
  }

  public function getTotalTimeSpent($courseId, $userId)
  {
    
    $assessments = $this->assessments()->pluck('id');

    $time_spent = 0;

    $time_spent += StudentModuleProgress::whereIn('model_id', $assessments)
      ->where('model_type', 'App\\Models\\Assessment')
      ->where('user_id', $userId)
      ->where('course_id', $courseId)
      ->sum('time_spent');

    $topics = $this->topics()->pluck('id');

    $time_spent += StudentModuleProgress::whereIn('model_id', $topics)
      ->where('model_type', 'App\\Models\\Topic')
      ->where('user_id', $userId)
      ->where('course_id', $courseId)
      ->sum('time_spent');

    $sub_topics = $this->sub_topics()->pluck('id');

    $time_spent += StudentModuleProgress::whereIn('model_id', $sub_topics)
      ->where('model_type', 'App\\Models\\SubTopic')
      ->where('user_id', $userId)
      ->where('course_id', $courseId)
      ->sum('time_spent');

    $course_evaluations = $this->course_evaluations()->pluck('id');

    $time_spent += StudentModuleProgress::whereIn('model_id', $course_evaluations)
      ->where('model_type', 'App\\Models\\CourseEvaluation')
      ->where('user_id', $userId)
      ->where('course_id', $courseId)
      ->sum('time_spent');

    $module_evaluations =  $this->module_evaluations->pluck('id');
    $time_spent += StudentModuleProgress::whereIn('model_id', $module_evaluations)
      ->where('model_type', 'App\\Models\\ModuleEvaluation')
      ->where('user_id', $userId)
      ->where('course_id', $courseId)
      ->sum('time_spent');
    

    $group_activities =  $this->group_activities->pluck('id');
    $time_spent += StudentModuleProgress::whereIn('model_id', $group_activities)
      ->where('model_type', 'App\\Models\\GroupActivity')
      ->where('user_id', $userId)
      ->where('course_id', $courseId)
      ->sum('time_spent');

    return $time_spent;
  }

  public function getTotalDoneAssessments($userId)
  {
    $assessments = $this->assessments()->pluck('id');

    $total = StudentModuleProgress::whereIn('model_id', $assessments)
      ->where('model_type', 'App\\Models\\Assessment')
      ->where('user_id', $userId)
      ->distinct('model_id')
      ->done()
      ->count();

    return $total;
  }

  public function getTotalDoneTopics($userId)
  {
    $topics = $this->topics()->pluck('id');

    $total = StudentModuleProgress::whereIn('model_id', $topics)
      ->where('model_type', 'App\\Models\\Topic')
      ->where('user_id', $userId)
      ->distinct('model_id')
      ->done()
      ->count();

    return $total;
  }
  
  public function getTotalDoneSubTopics($userId)
  {
    $sub_topics = $this->sub_topics()->pluck('id');

    $total = StudentModuleProgress::whereIn('model_id', $sub_topics)
      ->where('model_type', 'App\\Models\\SubTopic')
      ->where('user_id', $userId)
      ->distinct('model_id')
      ->done()
      ->count();

    return $total;
  }

  public function getTotalDoneCourseEvaluations($userId)
  {
    $course_evaluations = $this->course_evaluations()->pluck('id');

    $total = StudentModuleProgress::whereIn('model_id', $course_evaluations)
      ->where('model_type', 'App\\Models\\CourseEvaluation')
      ->where('user_id', $userId)
      ->distinct('model_id')
      ->done()
      ->count();

    return $total;
  }

  public function getTotalDoneModuleEvaluations($userId)
  {
    
      $module_evaluations =  $this->module_evaluations->pluck('id');
      
      $total = StudentModuleProgress::whereIn('model_id', $module_evaluations)
        ->where('model_type', 'App\\Models\\ModuleEvaluation')
        ->where('user_id', $userId)
        ->distinct('model_id')
        ->done()
        ->count();
  
      return $total;
  }

  public function getTotalAttendedZoomMeetings($userId)
  {
    $total = ZoomMeetingAttendee::whereIn('zoom_meeting_id', $this->zoom_meetings()->pluck('id'))
      ->where('present', 1)
      ->where('user_id', $userId)
      ->count();

    return $total;
  }

  public function getTotalDoneGroupActivities($userId)
  {
    
    $group_activities =  $this->group_activities->pluck('id');
    
    $total = StudentModuleProgress::whereIn('model_id', $group_activities)
      ->where('model_type', 'App\\Models\\GroupActivity')
      ->where('user_id', $userId)
      ->distinct('model_id')
      ->done()
      ->count();

    return $total;

  }

  public function completed_course()
  {
    return $this->hasMany(StudentCompleteCourse::class)
      ->whereHas('user', function($query){
          $query->whereHas('enrollments', function($subquery) {
              $subquery->where('course_id', $this->id);
          });
      });
  }

  public function getUserIsEnrolledAttribute()
  {
    return $this->enrollments()->where('user_id', auth()->guard('api')->id())->exists();
  }

  public function getCourseIsOpenForRegistrationAttribute()
  {
    return true;
    
    if (!$this->available_until || ( !$this->available_until['start_date'] && !$this->available_until['end_date'] )) {
      return true;
    }

    try {
      $currentDate = Carbon::now('UTC')->startOfDay();
      $startDate = Carbon::createFromFormat('Y-m-d', $this->available_until['start_date'], 'UTC')->startOfDay();
      $endDate = Carbon::createFromFormat('Y-m-d', $this->available_until['end_date'], 'UTC')->startOfDay();
  
      return $currentDate->between($startDate, $endDate);
    } catch (\Exception $e) {
      return true;
    }

  }

  public function scorm_activities()
  {
    return $this->hasMany(ScormActivity::class, 'course_id');
  }

  public function scorm()
  {
    return $this->hasMany(Scorm::class, 'course_id');
  }

  public function training_credit()
  {
    return $this->belongsTo(TrainingCredit::class, 'training_credits');
  }
  
  public function course_has_group_chat()
  {
      return $this->belongsToMany(GroupChat::class, 'course_has_group_chat');
  }
  
  public function course_has_course_group()
  {
      return $this->belongsToMany(CourseGroup::class, 'course_has_course_group');
  }

  public function getPaginatedGroupChats($paginate)
  {
      return $this->course_has_group_chat()->with('group_chat_users')->withCount('group_chat_users')->paginate($paginate ?? 10);
  }

}
