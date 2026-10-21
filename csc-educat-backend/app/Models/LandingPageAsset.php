<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandingPageAsset extends Model
{
    use HasFactory, SoftDeletes;

    const ASSET_BG = 'background';

    protected $fillable = [
        'asset_key',
        'uploader_id'
    ];
    
    protected $hidden = [
      'deleted_at'
    ];

    public function image()
    {
      return $this->morphOne(Image::class, 'imageable');
    }

}
