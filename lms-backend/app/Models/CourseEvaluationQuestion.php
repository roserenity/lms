<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseEvaluationQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_evaluation_id',
        'question',
        'type_of_question',
        'is_require_to_answer',
        'sort_no',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function image()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function course_evaluation()
    {
        return $this->belongsTo(CourseEvaluation::class);
    }

    public function answer()
    {
        return $this->hasOne(StudentCourseEvaluationAnswer::class);
    }
}
