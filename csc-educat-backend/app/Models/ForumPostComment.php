<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumPostComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'parent_id',
        'comment',
        'commentable_id',
        'commentable_type'
    ];

    protected $with = ['replies', 'user', 'attachments'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function replies()
    {
        return $this->hasMany(ForumPostComment::class, 'parent_id')->orderBy('created_at', 'DESC');
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
