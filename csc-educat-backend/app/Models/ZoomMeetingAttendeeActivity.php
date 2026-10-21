<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoomMeetingAttendeeActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'zoom_meeting_attendee_id',
        'score',
        'comment'
    ];

    public function zoom_meeting_attendee()
    {
        return $this->belongsTo(ZoomMeetingAttendee::class);
    }

}
