<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupActivityGroupMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_activity_id',
        'user_id',
    ];
    
    protected $hidden = [
        'deleted_at'
    ];

    public function groupActivityGroup()
    {
        return $this->belongsTo(GroupActivityGroup::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
