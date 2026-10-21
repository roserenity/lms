<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseProgress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'user_id',
        'total_done_topics',
        'total_done_sub_topics',
        'total_done_assessments',
        'total_done_course_evaluations',
        'total_done_module_evaluations',
        'total_done_group_activities',
        'total_attended_zoom_meetings',
        'total_time_spent',
        'progress'
    ];
    
}
