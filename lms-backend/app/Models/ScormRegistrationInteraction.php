<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScormRegistrationInteraction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scorm_registration_id',
        'user_id',
        'type',
        'interaction_id',
        'interaction_type',
        'interaction_timestamp',
        'correct_responses',
        'point_value',
        'learner_response',
        'result',
        'latency',
        'objectives',
        'data'
    ];

    protected $hidden = [
      'deleted_at'
    ];

    public function user()
    {
      return $this->belongsTo(User::class, "user_id");
    }

    public function scorm_progress()
    {
      return $this->hasOne(ScormRegistrationProgress::class);
    }

    public function scorm_registration()
    {
      return $this->hasOne(ScormRegistration::class);
    }

}
