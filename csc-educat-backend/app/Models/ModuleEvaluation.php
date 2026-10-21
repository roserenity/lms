<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Carbon;

class ModuleEvaluation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'module_id',
        'title',
        'description',
        'is_shuffle_questions',
        'is_send_feedback_to_user',
        'submission_limit',
        'is_allow_to_review',
        'uploader_id',
        'validity',
        'available_from',
        'available_to',
    ];

    protected $appends = [
        'module_evaluation_is_locked',
        'student_done_status'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function module_evaluation_questions()
    {
        return $this->hasMany(ModuleEvaluationQuestion::class);
    }

    public function student_module_evaluations()
    {
        return $this->hasMany(StudentModuleEvaluation::class);
    }

    public function student_module_evaluation_answers()
    {
        return $this->hasMany(StudentModuleEvaluationAnswer::class);
    }

    public function student_module_evaluation_progress()
    {
      return $this->morphOne(StudentModuleProgress::class, 'model');
    }

    public function getStudentModuleEvaluationStatus($user_id)
    {
      return $this->student_module_evaluation_progress()->where('user_id', $user_id)->where('status', StudentModuleProgress::STATUS_DONE)->count() > 0 ? true : false;
    }

    public function getStudentModuleEvaluationProgress($user_id)
    {
      return $this->student_module_evaluation_progress()->where('user_id', $user_id)->first();
    }

    public function getModuleEvaluationIsLockedAttribute()
    {
      // If validity is anytime, lock, or not set, then the module is not locked
        if (in_array($this->validity, ['anytime', 'lock', null])) {
            return false;
        }

        try {
            $currentDate = Carbon::now('Asia/Manila')->startOfDay();
            $availableFromDate = $this->available_from 
                ? Carbon::createFromFormat('Y-m-d', $this->available_from, 'Asia/Manila')->startOfDay() 
                : null;
            $availableToDate = $this->available_to 
                ? Carbon::createFromFormat('Y-m-d', $this->available_to, 'Asia/Manila')->startOfDay() 
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

    public function getStudentDoneStatusAttribute()
    {
      return $this->student_module_evaluation_progress()->where('status', StudentModuleProgress::STATUS_DONE)->count() > 0 ? true : false;
    }

    public function module_content()
    {
        return $this->morphOne(ModuleContent::class, 'contentable');
    }
}
