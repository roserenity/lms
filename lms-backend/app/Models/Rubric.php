<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rubric extends Model
{
    use HasFactory;

    protected $fillable = [
      'uuid',
      'title',
      'type_of_rubric',
      'description',
      'uploader_id'
    ];

    protected $hidden = [
      'deleted_at'
    ];

    public function uploader()
    {
      return $this->belongsTo(User::class);
    }

    public function criterias()
    {
      return $this->hasMany(RubricCriteria::class);
    }

    public function ratings()
    {
      return $this->hasMany(RubricRating::class)->orderByDesc('rate');
    }

    // public function rubric_ratings()
    // {
    //   return $this->hasMany(RubricRating::class)->orderByDesc('rating_number')->groupBy('rating_number');
    // }

    public function shared_rubrics()
    {
      return $this->hasMany(ShareRubric::class);
    }
    
}
