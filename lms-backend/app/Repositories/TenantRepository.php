<?php

namespace App\Repositories;

use App\Models\Central\TenantInfo;
use App\Models\Central\Tenant;
use App\Models\User;

class TenantRepository extends BaseRepository
{
  protected $model;

  public function __construct(TenantInfo $model)
  {
    $this->model = $model;
  }

  public function allInfo()
  {
    return $this->model->latest()->get();
  }

  public function storeTenant($id, $domain)
  {
    $occupant = Tenant::create(['id' => $id]);
    $occupant->domains()->create([
      'domain' => $domain
    ]);
  }

  public function showTenant($id)
  {
    return Tenant::with(['info', 'domains'])
      ->where('id', $id)
      ->first();
  }

  public function activateTenant($id): bool
  {
    return Tenant::where('id', $id)->update([
      'status' => 2
    ]);
  }
}
