<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareRubric extends Model
{
    use HasFactory;

    protected $fillable = [
      'rubric_id',
      'shared_by_user_id',
      'shared_to_user_id'
    ];

    public function rubric()
    {
      return $this->belongsTo(Rubric::class);
    }

    public function shared_by_user()
    {
      return $this->belongsTo(User::class);
    }

    public function shared_to_user()
    {
      return $this->belongsTo(User::class);
    }
}
