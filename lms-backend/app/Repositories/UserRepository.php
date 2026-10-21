<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
  protected $model;

  public function __construct(User $model)
  {
    $this->model = $model;
  }

  public function getByRole($role)
  {
    if($role == $this->model::ROLE_USER) return $this->model::user()->get();
    if($role == $this->model::ROLE_TEACHER) return $this->model::teacher()->get();
    if($role == $this->model::ROLE_ADMIN) return $this->model::admin()->get();
  }
}
