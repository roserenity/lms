<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scorm extends Model
{
    use HasFactory, SoftDeletes;

    const COMPLETED = 'COMPLETE';
    const RUNNING = 'RUNNING';
    const FAILED = 'ERROR';

    protected $fillable = [
      'course_id',
      'model_id',
      'model_type',
      'scorm_cloud_course_id',
      'title',
      'status',
      'job_id',
      'data',
      'preview_link',
      'errors'
    ];

    protected $hidden = [
      'deleted_at'
    ];

    public function model()
    {
        return $this->morphTo();
    }

    public function learner_registrations()
    {
      return $this->hasMany(ScormRegistration::class, "scorm_id");
    }

    public function my_registration()
    {
      return $this->hasOne(ScormRegistration::class, "scorm_id")->where('user_id', auth()->guard('api')->id());
    }
}
