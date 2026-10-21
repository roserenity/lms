<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleContent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['module_id', 'contentable_type', 'contentable_id', 'sort_no'];

    public function contentable()
    {
        return $this->morphTo();
    }

    protected $hidden = [
      'deleted_at'
    ];
}

