<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoomMeetingAttendee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'zoom_meeting_id',
        'user_id',
        'present'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zoom_meeting()
    {
        return $this->belongsTo(ZoomMeeting::class);
    }
    
    public function zoom_meeting_activity()
    {
        return $this->hasOne(ZoomMeetingAttendeeActivity::class);
    }

}
