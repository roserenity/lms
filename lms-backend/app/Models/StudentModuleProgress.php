<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentModuleProgress extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DONE = 'DONE';
    const STATUS_IN_PROGRESS = 'IN-PROGRESS';

    protected $fillable = [
        'model_id',
        'model_type',
        'course_id',
        'user_id',
        'time_spent',
        'status'
    ];

    public function model()
    {
        return $this->morphTo();
    }
    
    public function scopeDone($query)
    {
        return $query->where('status', StudentModuleProgress::STATUS_DONE);
    }
    
    public function scopeInProgress($query)
    {
        return $query->where('status', StudentModuleProgress::STATUS_IN_PROGRESS);
    }
}
