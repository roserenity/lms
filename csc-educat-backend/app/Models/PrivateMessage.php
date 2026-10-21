<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrivateMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'private_chat_id',
        'message_body',
        'to_user_id',
        'from_user_id'
    ];

    protected $with = ['attachments', 'sender'];

    public function private_chat()
    {
        return $this->belongsTo(PrivateChat::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}
