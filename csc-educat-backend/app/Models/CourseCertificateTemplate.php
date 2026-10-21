<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCertificateTemplate extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'course_id',
    'certificate_template_id'
  ];

  protected $hidden = [
    'deleted_at'
  ];

  public function course()
  {
    return $this->belongsTo(Course::class);
  }

  public function certificate_template()
  {
    return $this->belongsTo(CertificateTemplate::class);
  }

}
