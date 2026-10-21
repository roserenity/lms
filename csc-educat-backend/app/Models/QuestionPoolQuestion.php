<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionPoolQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
      'question_pool_id',
      'question',
      'type_of_question',
      'score',
      'require_file_attachment',
      'require_support_answer',
      'is_require_to_answer',
      'rubric_id',
      'sort_no',
      'data',
      'uploader_id'
    ];
  
    protected $hidden = [
      'deleted_at'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function question_pool()
    {
        return $this->belongsTo(QuestionPool::class);
    }

    public function answer()
    {
        return $this->hasOne(QuestionPoolQuestionAnswer::class);
    }

    public function image()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    
}
