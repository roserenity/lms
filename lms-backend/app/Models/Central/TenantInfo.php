<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantInfo extends Model
{
  use HasFactory;

  protected $fillable = [
    'tenant_id',
    'contact_person',
    'email',
    'organization',
    'mobile_no',
    'tel_no',
  ];

  public function tenant()
  {
    return $this->belongsTo(Tenant::class, 'tenant_id');
  }
}
