<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exports extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'filename', 'path', 'url', 'status', 'exported_at'
    ];

}
