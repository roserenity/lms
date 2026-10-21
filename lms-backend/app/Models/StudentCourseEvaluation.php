<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseEvaluation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'course_evaluation_id',
        'is_checked'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course_evaluation()
    {
        return $this->belongsTo(CourseEvaluation::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentCourseEvaluationAnswer::class);
    }
}
