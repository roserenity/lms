<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'user_id',
    'course_id'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  public function scopeEnrolled($query)
  {
    return $query->where('user_id', auth()->guard('api')->user()->id);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function course()
  {
    return $this->belongsTo(Course::class);
  }

  public function transactions()
  {
    return $this->hasMany(Invoice::class, 'user_id', 'user_id')
      ->whereColumn('course_id', 'enrollments.course_id');
  }

}
