<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumPost extends Model
{
    use HasFactory, SoftDeletes;

    const IS_APPROVED = 'APPROVED';
    const IS_PENDING = 'PENDING';
    const IS_DECLINED = 'DECLINED';
    const IS_REMOVED = 'REMOVED';

    protected $fillable = [
        'course_id',
        'user_id',
        'topic_category',
        'title',
        'content',
        'status',
        'is_pinned',
        'reviewer_id'
    ];

    protected $with = ['user', 'attachments'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function comments()
    {
        return $this->morphMany(ForumPostComment::class, 'commentable')->whereNull('parent_id')->orderBy('created_at', 'DESC');
    }
    
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')
            ->orderByRaw("
                CASE 
                    WHEN file_type IN ('jpg', 'jpeg', 'png') THEN 1 
                    ELSE 0 
                END
            ");
    }

}
