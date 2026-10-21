<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationTable extends Model
{
  use HasFactory;
  protected $fillable = [
    'table_name',
    'sort',
  ];
}
