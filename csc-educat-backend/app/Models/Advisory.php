<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advisory extends Model
{
    use HasFactory, SoftDeletes;

    const IS_ACTIVE = 1;
    const IS_INACTIVE = 0;

    protected $hidden = [
      'deleted_at',
    ];

    protected $fillable = [
        'sort_no',
        'active',
        'uploader_id'
    ];

    protected $with = [
      'image'
    ];

    public function image() 
    {
      return $this->morphOne(Image::class, 'imageable');
    }
}
