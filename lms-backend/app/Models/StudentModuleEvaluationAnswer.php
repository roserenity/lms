<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentModuleEvaluationAnswer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'student_module_evaluation_id',
        'module_evaluation_question_id',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function module_evaluation()
    {
        return $this->belongsTo(ModuleEvaluation::class);
    }

    public function module_evaluation_question()
    {
        return $this->belongsTo(ModuleEvaluationQuestion::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
