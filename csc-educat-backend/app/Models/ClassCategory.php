<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassCategory extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'class_code',
    'class_name'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  public function courses()
  {
    return $this->belongsToMany(Course::class, 'course_has_classes');
  }
}
