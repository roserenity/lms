<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCertificateAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'certificate_category_id',
        'course_id'
    ];
    
    protected $hidden = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function certificateCategory()
    {
        return $this->belongsTo(CertificateTemplateCategory::class, 'certificate_category_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
