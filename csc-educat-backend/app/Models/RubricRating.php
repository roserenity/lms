<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RubricRating extends Model
{
    use HasFactory;

    protected $fillable = [
      'uuid',
      'rubric_id',
      // 'rubric_criteria_id',
      'rate',
      'description'
    ];

    protected $hidden = [
      'deleted_at'
    ];

    protected $with = ['indicators'];

    public function indicators()
    {
      return $this->hasMany(CriteriaIndicator::class);
    }
}
