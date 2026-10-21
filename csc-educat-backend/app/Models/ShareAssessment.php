<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
      'assessment_id',
      'shared_by_user_id',
      'shared_to_user_id'
    ];

    public function assessment()
    {
      return $this->belongsTo(Assessment::class);
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
