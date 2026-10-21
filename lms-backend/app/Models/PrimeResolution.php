<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrimeResolution extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'invoice_id',
        'url'
    ];

    protected $hidden = [
      'deleted_at'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
