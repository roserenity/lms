<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    CONST TICKET_OPEN = 1;
    CONST TICKET_PENDING = 2;
    CONST TICKET_ON_HOLD = 3;
    CONST TICKET_SOLVED = 4;
    CONST TICKET_CLOSED = 5;

    protected $fillable = [
        'user_id',
        'concern_category',
        'content',
        'status'
    ];

    protected $with = ['file_attachment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function file_attachment()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function ticket_messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function scopeOpen($query)
    {
      return $query->where('status', Ticket::TICKET_OPEN);
    }

    public function scopePending($query)
    {
      return $query->where('status', Ticket::TICKET_PENDING);
    }

    public function scopeOnHold($query)
    {
      return $query->where('status', Ticket::TICKET_ON_HOLD);
    }

    public function scopeSolved($query)
    {
      return $query->where('status', Ticket::TICKET_SOLVED);
    }

    public function scopeClosed($query)
    {
      return $query->where('status', Ticket::TICKET_CLOSED);
    }
}
