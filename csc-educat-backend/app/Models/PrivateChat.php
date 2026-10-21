<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User;

class PrivateChat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'creator_id',
        'user_id'
    ];

    // protected $hidden = ['creator_id', 'user_id'];

    protected $appends = ['sender', 'receiver'];

    public function getSenderAttribute()
    {
        if ($this->attributes['user_id'] == auth()->guard('api')->user()->id) {
            return User::select(['id', 'first_name', 'last_name', 'suffix'])->find($this->attributes['user_id']) ?? NULL;
        } else if ($this->attributes['creator_id'] == auth()->guard('api')->user()->id) {
            return User::select(['id', 'first_name', 'last_name', 'suffix'])->find($this->attributes['creator_id']) ?? NULL;
        }
    }

    public function getReceiverAttribute()
    {
        if ($this->attributes['user_id'] != auth()->guard('api')->user()->id) {
            return User::select(['id', 'first_name', 'last_name', 'suffix'])->find($this->attributes['user_id']) ?? NULL;
        } else if ($this->attributes['creator_id'] != auth()->guard('api')->user()->id) {
            return User::select(['id', 'first_name', 'last_name', 'suffix'])->find($this->attributes['creator_id']) ?? NULL;
        }
    }

    public function private_messages()
    {
        return $this->hasMany(PrivateMessage::class);
    }

    public function latest_private_message()
    {
        return $this->hasOne(PrivateMessage::class)->latest();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
