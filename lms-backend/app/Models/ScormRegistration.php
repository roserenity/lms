<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScormRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scorm_id',
        'user_id',
        'scorm_cloud_course_id',
        'registration_id',
        'launch_link',
        'scorm_registration_progress_id'
    ];

    protected $hidden = [
      'deleted_at'
    ];

    public function user()
    {
      return $this->belongsTo(User::class, "user_id");
    }

    public function scorm()
    {
      return $this->belongsTo(Scorm::class, "scorm_id");
    }

    public function scorm_progress()
    {
      return $this->hasOne(ScormRegistrationProgress::class);
    }

    public function scorm_interactions()
    {
      return $this->hasMany(ScormRegistrationInteraction::class);
    }

}
