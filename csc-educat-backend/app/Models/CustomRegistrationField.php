<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomRegistrationField extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'custom_registration_page_id',
        'input_field_label',
        'input_field_name',
        'input_field_type',
        'is_required',
        'is_multiple',
        'sort_no',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    protected $hidden = [
      'deleted_at'
    ];

    public function custom_registration_page()
    {
        return $this->belongsTo(CustomRegistrationPage::class);
    }
}
