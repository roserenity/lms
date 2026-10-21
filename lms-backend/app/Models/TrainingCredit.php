<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingCredit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'uploader_id',
    ];
    
    protected $hidden = [
        'deleted_at'
    ];
}
