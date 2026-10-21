<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RubricCriteria extends Model
{
    use HasFactory;

    protected $fillable = [
      'uuid',
      'rubric_id',
      'title',
      'score'
    ];

    protected $hidden = [
      'deleted_at'
    ];

    protected $with = ['indicators'];

    public function indicators()
    {
      return $this->hasMany(CriteriaIndicator::class);
    }

    // public function rubric()
    // {
    //   return $this->belongsTo(Rubric::class);
    // }

    // public function rubric_ratings()
    // {
    //   return $this->hasMany(RubricRating::class)->orderBy('rating_number', 'DESC');
    // }


}
