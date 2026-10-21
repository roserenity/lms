<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
  use HasApiTokens, HasFactory, Notifiable, SoftDeletes;


  const ROLE_USER     = 'USER';
  const ROLE_INSTRUCTOR  = 'INSTRUCTOR';
  const ROLE_ADMIN    = 'ADMIN';

  protected $fillable = [
    'uuid',
    'enrollee_id',
    'first_name',
    'middle_name',
    'last_name',
    'suffix',
    'gender',
    'role',
    'status',
    'is_first_login',
    'email',
    'password',
    'data'
  ];

  protected $hidden = [
    'password',
    'remember_token',
    'deleted_at',
  ];

  protected $casts = [
    'data' => 'array',
    'email_verified_at' => 'datetime'
  ];

  protected $appends = ['full_name'];

  protected $with = ['image'];

  protected static function booted()
  {
    static::creating(function ($user) {
      do {
          $enrolleeId = 'LMS' . substr(md5(uniqid()), 0, 5) . str_pad(mt_rand(0, 9999), 5, '0', STR_PAD_LEFT);
      } while (self::where('enrollee_id', $enrolleeId)->exists());

      $user->enrollee_id = $enrolleeId;
    });
  }

  public function setPasswordAttribute($password)
  {
    if (Hash::needsRehash($password)) {
      $password = Hash::make($password);
    }

    $this->attributes['password'] = $password;
  }

  public function getFullNameAttribute()
  {
      $initials = [];

      if (!empty($this->middle_name)) {
          $names = explode(' ', trim($this->middle_name));

          foreach ($names as $name) {
              if (!empty($name)) {
                  $initials[] = strtoupper($name[0]);
              }
          }
      }

      $middle_initials = !empty($initials) ? implode('. ', $initials) . '.' : '';

      $parts = array_filter([
          $this->first_name,
          $middle_initials,
          $this->last_name,
          $this->suffix
      ]);

      return ucwords(implode(' ', $parts));
  }


  public function scopeUser($query)
  {
    return $query->where('role', User::ROLE_USER);
  }

  public function scopeTeacher($query)
  {
    return $query->where('role', User::ROLE_INSTRUCTOR);
  }

  public function scopeAdmin($query)
  {
    return $query->where('role', User::ROLE_ADMIN);
  }

  public function image()
  {
    return $this->morphOne(Image::class, 'imageable');
  }

  public function courses()
  {
    return $this->hasMany(Course::class, 'uploader_id');
  }

  public function enrollments()
  {
    return $this->hasMany(Enrollment::class);
  }

  public function user_has_assessments()
  {
    return $this->belongsToMany(Assessment::class, 'assessment_has_users');
  }

  public function assessments()
  {
    return $this->hasMany(Assessment::class, 'uploader_id');
  }

  public function shared_assessments()
  {
    return $this->hasMany(ShareAssessment::class, 'shared_by_user_id');
  }

  public function private_chat_as_creator()
  {
    return $this->hasOne(PrivateChat::class, 'creator_id');
  }

  public function private_chat_as_user()
  {
    return $this->hasOne(PrivateChat::class, 'user_id');
  }

  public function group_chats()
  {
    return $this->belongsToMany(GroupChat::class, 'group_chat_has_users');
  }

  public function course_groups()
  {
    return $this->belongsToMany(CourseGroup::class, 'course_group_has_users');
  }

  public function tickets()
  {
    return $this->hasMany(Ticket::class);
  }

  public function user_has_course_favorites()
  {
    return $this->belongsToMany(Course::class, 'course_favorite_has_users')->where('status', Course::IS_PUBLISH)->withCount(['assessments', 'topics', 'sub_topics']);
  }

  public function assessment_scores()
  {
    return $this->hasMany(AssessmentScore::class, 'user_id');
  }

  public function zoom_meetings()
  {
    return $this->hasMany(ZoomMeeting::class);
  }

  public function shared_courses()
  {
    return $this->belongsToMany(Course::class, 'share_course_has_users');
  }

  public function question_answer_feedback()
  {
    return $this->hasOne(QuestionAnswerFeedback::class);
  }

  public function invoices()
  {
    return $this->hasMany(Invoice::class);
  }

  public function student_course_evaluations()
  {
    return $this->hasMany(StudentCourseEvaluation::class);
  }

  public function student_module_evaluations()
  {
    return $this->hasMany(StudentModuleEvaluation::class);
  }

  public function cart_item_courses()
  {
    return $this->belongsToMany(Course::class, 'cart_items')->select(['id', 'title', 'price', 'is_free', 'available_until', 'uuid'])->where('status', Course::IS_PUBLISH);
  }

  public function completed_course()
  {
    return $this->hasMany(StudentCompleteCourse::class);
  }

  public function certificates()
  {
      return $this->hasMany(Certificate::class,'user_id');
  }

  public function course_progress()
  {
    return $this->hasMany(StudentCourseProgress::class, 'user_id');
  }

  public function certificate_template_categories()
  {
    return $this->belongsToMany(CertificateTemplateCategory::class, 'student_certificate_assignments', 'user_id', 'certificate_category_id')->withPivot('course_id');
  }

  public function getTicketCount()
  {
    return Ticket::pending()->count() + Ticket::open()->count();
  }

  public function group_activity_has_group_members(){
    return $this->belongsToMany(GroupActivityGroup::class, 'group_activity_has_group_members', 'user_id', 'group_activity_group_id')
        ->withPivot('group_activity_id')
        ->withTimestamps();
  }
}
