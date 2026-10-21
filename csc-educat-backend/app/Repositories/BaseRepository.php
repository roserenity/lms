<?php

namespace App\Repositories;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
  protected $model;

  public function __construct(Model $model)
  {
    $this->model = $model;
  }

  public function create(array $payload): ?Model
  {
    $model = $this->model::create($payload);
    return $model->fresh();
  }

  public function show(int $id): ?Model
  {
    return $this->model::findOrFail($id);
  }

  public function update(int $id, array $payload): bool
  {
    $model = $this->model::findOrFail($id);
    return $model->update($payload);
  }

  public function delete(int $id): bool
  {
    return $this->model::findOrFail($id)->delete();
  }

  public function sort(array $payload): void
  {
    foreach ($payload['sorted'] as $key => $value) {
      $this->model::findOrFail($value['id'])->update([
        'sort' => $key
      ]);
    }
  }

  public function findUid(string $uuid): ?Model
  {
    return $this->model::firstWhere('uuid', $uuid);
  }

  public function createAssessment(string $uuid, array $payload): ?Model
  {
    $data = $this->findUid($uuid);
    $model = $data->assessment->create($payload);
    return $model->fresh();
  }
}

