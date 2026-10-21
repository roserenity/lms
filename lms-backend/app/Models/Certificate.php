<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'course_id',
    ];

    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function course()
    {
      return $this->belongsTo(Course::class);
    }

    public function image()
    {
      return $this->morphOne(Image::class, 'imageable');
    }
}
