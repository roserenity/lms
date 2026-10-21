<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCardColor extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'color'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
