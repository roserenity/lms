<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'ticket_id',
        'content',
        'to_user_id',
        'from_user_id'
    ];

    protected $with = ['file_attachment'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function file_attachment()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function to_user()
    {
      return $this->belongsTo(User::class);
    }

    public function from_user()
    {
      return $this->belongsTo(User::class);
    }
}
