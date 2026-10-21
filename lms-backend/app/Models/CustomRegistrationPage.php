<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomRegistrationPage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'sort_no'
    ];

    protected $hidden = [
      'deleted_at'
    ];

    protected $with = ['custom_registration_fields'];

    public function custom_registration_fields()
    {
        return $this->hasMany(CustomRegistrationField::class)->select(['id', 'custom_registration_page_id', 'data', 'input_field_label', 'input_field_name', 'input_field_type', 'is_multiple', 'is_required', 'sort_no'])->orderBy('sort_no', 'ASC');
    }
}
