<?php

namespace App\Repositories;

use App\Models\Topic;

class TopicRepository extends BaseRepository
{
  protected $model;

  public function __construct(Topic $model)
  {
    $this->model = $model;
  }

  public function max($id)
  {
    return $this->model::where('course_id', $id)->max('sort');
  }
}
