<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionPool extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
      'name',
      'type_of_question_pool',
      'uploader_id',
    ];
  
    protected $hidden = [
      'deleted_at'
    ];

    public function questions()
    {
      return $this->hasMany(QuestionPoolQuestion::class)->orderBy('sort_no', 'asc');
    }

    public function uploader()
    {
      return $this->belongsTo(User::class);
    }
}
