<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseEvaluationAnswer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'student_course_evaluation_id',
        'course_evaluation_question_id',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function student_course_evaluation()
    {
        return $this->belongsTo(StudentCourseEvaluation::class);
    }

    public function course_evaluation_question()
    {
        return $this->belongsTo(CourseEvaluationQuestion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
