<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomModuleField extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_DROPDOWN = 'dropdown';
    const TYPE_TEXTAREA = 'text_area';
    const TYPE_TEXTFIELD = 'text_field';
    const TYPE_CALENDAR = 'calendar';
    const TYPE_NUMBER = 'number';

    protected $fillable = [
        'input_field_label',
        'input_field_name',
        'input_field_type',
        'is_multiple',
        'sort_no',
        'data',
        'uploader_id'
    ];

    protected $hidden = [
      'deleted_at'
    ];

}
