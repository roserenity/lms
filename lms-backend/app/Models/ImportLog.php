<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'type',
        'message',
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function course()
    {
      return $this->belongsTo(Course::class);
    }
}
