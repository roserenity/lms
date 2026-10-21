<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Str;

class CourseGroupGroupActivity extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'uuid',
        'course_group_id',
        'group_activity_id',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public function course_group()
    {
        return $this->belongsTo(CourseGroup::class);
    }

    public function group_activity()
    {
        return $this->belongsTo(GroupActivity::class);
    }

}
