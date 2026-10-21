<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupChat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'user_id'
    ];

    protected $with = ['image'];

    protected $appends = [
        'user_is_member',
    ];

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function group_messages()
    {
        return $this->hasMany(GroupMessage::class);
    }

    public function latest_group_message()
    {
        return $this->hasOne(GroupMessage::class)->latest();
    }

    public function group_chat_users()
    {
        return $this->belongsToMany(User::class, 'group_chat_has_users');
    }

    public function group_chat_has_course_group()
    {
        return $this->belongsToMany(CourseGroup::class, 'course_group_has_group_chat');
    }

    public function getUserIsMemberAttribute()
    {
        $user_id = auth()->guard('api')->id();

        if (!$user_id) {
            return false;
        }
    
        return $this->group_chat_users()->where('user_id', $user_id)->exists()
            || $this->user_id === $user_id;
    }
    
    public function group_chat_has_course()
    {
        return $this->belongsToMany(Course::class, 'course_has_group_chat');
    }
  
}
