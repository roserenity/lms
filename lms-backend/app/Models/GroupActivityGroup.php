<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupActivityGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_activity_id',
        'name',
        'file_id',
        'file_url',
        'checked'
    ];

    protected $hidden = [
        'deleted_at'
    ];


    public function group_activity()
    {
        return $this->belongsTo(GroupActivity::class);
    }

    public function group_activity_has_group_members()
    {
        return $this->belongsToMany(User::class, 'group_activity_has_group_members');
    }
    
    public function group_has_group_activity()
    {
        return $this->belongsToMany(GroupActivity::class, 'group_activity_group');
    }
    
}
