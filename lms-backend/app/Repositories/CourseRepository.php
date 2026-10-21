<?php

namespace App\Repositories;

use App\Models\Course;

class CourseRepository extends BaseRepository
{
  protected $model;

  public function  __construct(Course $model)
  {
    $this->model = $model;
  }

  public function all()
  {
    return $this->model::exclusive()
      ->latest()
      ->get();
  }

  public function updateImage($data, $file) :void
  {
    $data->image()->updateOrCreate(
      ['imageable_id' => $data->id],
      ['image' => $file]
    );
  }
}
