<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_chat_id',
        'message_body',
        'synced_users',
        'sender_id'
    ];
    
    protected $with = ['attachments', 'sender'];

    public function group_chat()
    {
        return $this->belongsTo(GroupChat::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
