<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'course_id'
    ];

    public function course_group_has_users()
    {
        return $this->belongsToMany(User::class, 'course_group_has_users');

    }

    public function course_group_has_group_chat()
    {
        return $this->belongsToMany(GroupChat::class, 'course_group_has_group_chat');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'course_id');
    }

}