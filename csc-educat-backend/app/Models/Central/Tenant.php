<?php

namespace App\Models\Central;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
  use HasDatabase, HasDomains;

  protected $fillable = [
    'id',
    'tenant_name',
    'tenant_description',
    'status',
    'domain_name',
    'api_endpoint',
    'navigation_background_color',
    'navigation_banner_color',
    'navigation_link_color',
    'navigation_text_icon_color',
    'sidebar_color',
    'mini_navigation_background_color',
    'mini_navigation_link_color',
    'mini_navigation_icon_color',
    'primary_buttons_color',
  ];

  protected $with = ['images'];

  public static function getCustomColumns(): array
  {
    return [
      'id',
      'tenant_name',
      'tenant_description',
      'status',
      'domain_name',
      'api_endpoint',
    ];
  }

  public function info()
  {
    return $this->hasOne(TenantInfo::class, 'tenant_id');
  }

  public function images()
  {
    return $this->morphMany(Image::class, 'imageable');
  }
}
