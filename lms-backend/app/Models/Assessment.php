<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Carbon;

class Assessment extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'uuid',
    'course_id',
    'module_id',
    'uploader_id',
    'title',
    'type_of_assessment',
    'description',
    'require_checking',
    'shuffle_questions',
    'shuffle_answers',
    'time_limit',
    'max_attempt',
    'accepted_score',
    'passing_score',
    'view_results_by',
    'view_results_availability',
    'show_each_questions',
    'lock_question_after_answering',
    'due_date',
    'total_points'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  protected $appends = [
    'assessment_is_locked',
    'student_done_status',
    'assessment_questions_sum_score',
    'assessment_has_essay',
    'assessment_has_submissions'
  ];

  public function course()
  {
    return $this->belongsTo(Course::class);
  }

  public function module()
  {
    return $this->belongsTo(Module::class);
  }

  public function assessment_has_users()
  {
    return $this->belongsToMany(User::class, 'assessment_has_users');
  }

  public function assessment_questions()
  {
    return $this->hasMany(AssessmentQuestion::class)->orderBy('sort_no', 'asc');
  }
  
  //user score
  public function assessment_scores()
  {
    return $this->hasMany(AssessmentScore::class);
  }

  public function question_answers()
  {
    return $this->hasMany(QuestionAnswer::class);
  }

  public function uploader()
  {
    return $this->belongsTo(User::class);
  }

  public function shared_assessments()
  {
    return $this->hasMany(ShareAssessment::class);
  }

  public function assessment_has_topics()
  {
    return $this->belongsToMany(Topic::class, 'assessment_has_topics');
  }

  public function assessment_has_sub_topics()
  {
    return $this->belongsToMany(SubTopic::class, 'assessment_has_sub_topics');
  }

  public function student_module_progress()
  {
    return $this->morphMany(StudentModuleProgress::class, 'model');
  }

  public function getStudentAssessmentStatus($user_id)
  {
    return $this->student_module_progress()->where('user_id', $user_id)->count() > 0 ? true : false;
  }

  public function getStudentAssessmentProgress($user_id)
  {
    return $this->student_module_progress()->where('user_id', $user_id)->where('status', StudentModuleProgress::STATUS_DONE)->first();
  }

  public function assessment_score()
  {
    $assessment_scores = AssessmentScore::where('assessment_id', $this->id)
        ->where('user_id', $this->authUser()->id);
    
    $score = $assessment_scores->where('active', 1)->first();

    return [
        'score' =>  $score->score ?? null,
        'attempts' => $assessment_scores->count(),
        'created_at' => $score->created_at ?? null,
    ];
  }

  public function getAssessmentIsLockedAttribute()
  {
    if(!$this->due_date) return false;

    try {
      $currentDate = Carbon::now('UTC')->startOfDay();
      $validFromDate = Carbon::createFromFormat('Y-m-d', json_decode($this->due_date, true)['start'], 'UTC')->startOfDay();
      $validUntilDate = Carbon::createFromFormat('Y-m-d', json_decode($this->due_date, true)['end'], 'UTC')->startOfDay();
  
      if ($currentDate->greaterThanOrEqualTo($validFromDate) && $currentDate->lessThanOrEqualTo($validUntilDate)) {
        return false;
      } else return true;
    } catch (\Exception $e) {
        return false;
    }
  }

  public function student_assessment_progress()
  {
    return $this->morphOne(StudentModuleProgress::class, 'model')->where('user_id', auth()->guard('api')->id());
  }

  public function getStudentDoneStatusAttribute()
  {
    return $this->student_assessment_progress()->where('status', StudentModuleProgress::STATUS_DONE)->count() > 0 ? true : false;
  }

  public function module_content()
  {
      return $this->morphOne(ModuleContent::class, 'contentable');
  }

  public function getAssessmentQuestionsSumScoreAttribute()
  {
      return $this->assessment_questions()->sum('score');
  }

  public function getAssessmentHasEssayAttribute()
  {
    return $this->assessment_questions()
      ->where('type_of_question', 'essay')
      ->exists();
  }

  public function getAssessmentHasSubmissionsAttribute()
  {
    return $this->assessment_scores()
      ->where('active', 1)
      ->exists();
  }

}
