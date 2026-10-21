<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentModuleEvaluation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'module_evaluation_id',
        'is_checked'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module_evaluation()
    {
        return $this->belongsTo(ModuleEvaluation::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentModuleEvaluationAnswer::class);
    }
}
