<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
  protected $fillable = [
    'url',
    'imageable_id',
    'imageable_type'
  ];

  protected $casts = [
    'created_at' => 'date:Y-m-d h:i:s',
    'updated_at' => 'date:Y-m-d h:i:s'
  ];

  public function imageable()
  {
    return $this->morphTo();
  }
}
