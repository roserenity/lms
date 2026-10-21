<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateAttestor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'certificate_template_id',
        'name',
        'designation',
        'info'
    ];
    
    protected $hidden = [
      'deleted_at'
    ];

    public function certificate_template()
    {
      return $this->belongsTo(CertificateTemplate::class);
    }

    public function image()
    {
      return $this->morphOne(Image::class, 'imageable');
    }
}
