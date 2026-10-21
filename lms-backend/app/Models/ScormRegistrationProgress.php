<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScormRegistrationProgress extends Model
{
    use HasFactory, SoftDeletes;

    const IS_COMPLETED = 'COMPLETED';
    const IS_INCOMPLETE = 'INCOMPLETE';
    const IS_UNKNOWN = 'UNKNOWN';

    protected $fillable = [
        'scorm_registration_id',
        'user_id',
        'completion_status',
        'success_status',
        'completion_date',
        'total_seconds_tracked',
        'first_accessed_date',
        'last_accessed_date',
        'activity_details',
        'data'
    ];

    protected $appends = ['time_tracked'];
    
    protected $hidden = [
      'deleted_at'
    ];

    protected $casts = [
      'completion_date' => 'datetime',
    ];

    public function user()
    {
      return $this->belongsTo(User::class, "user_id");
    }

    public function scorm_registration()
    {
      return $this->belongsTo(ScormRegistration::class);
    }

    public function getTimeTrackedAttribute()
    {
      if($this->total_seconds_tracked) {
        $minutes = floor($this->total_seconds_tracked / 60);
        $seconds = $this->total_seconds_tracked % 60;

        if ($minutes > 0) {
            return "{$minutes} minute(s) and {$seconds} second(s)";
        } else {
            return "{$seconds} second(s)";
        }
        
      } else return '';
    }
}
