<?php

namespace App\Repositories;

use App\Models\SubTopic;

class SubTopicRepository extends BaseRepository
{
  protected $model;

  public function __construct(SubTopic $model)
  {
    $this->model = $model;
  }

  public function max($id)
  {
    return $this->model::where('topic_id', $id)->max('sort');
  }
}
