<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'uploader_id',
        'certificate_category_id',
        'is_default',
        'content'
    ];

    public function uploader()
    {
      return $this->belongsTo(User::class);
    }

    public function course()
    {
      return $this->belongsTo(Course::class);
    }

    public function logo()
    {
      return $this->hasOne(CertificateLogo::class);
    }

    public function attestor()
    {
      return $this->hasMany(CertificateAttestor::class);
    }

    public function signatory()
    {
      return $this->hasMany(CertificateSignatory::class);
    }

    public function certificate_template_has_courses()
    {
      return $this->hasMany(CourseCertificateTemplate::class);
    }
}