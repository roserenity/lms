<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
      'rubric_criteria_id',
      'rubric_rating_id',
      'body',
    ];
}
