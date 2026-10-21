<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScormActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
      'course_id',
      'model_id',
      'model_type',
      'title',
      'activity_id',
      'passing_score',
    ];

    public function scorm_questions()
    {
        return $this->hasMany(ScormQuestion::class);
    }

    public function scorm_scores()
    {
        return $this->hasMany(ScormActivityScore::class);
    }

    public function scorm_answers()
    {
      return $this->hasMany(ScormActivityAnswer::class);
    }

    public function my_scorm_answers()
    {
      return $this->hasMany(ScormActivityAnswer::class)->where('user_id', auth()->guard('api')->user()->id);
    }

    public function model()
    {
        return $this->morphTo();
    }

}
