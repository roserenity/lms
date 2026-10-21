<?php

namespace App\Services\Tenants;
use Intervention\Image\Facades\Image;

class CourseService
{
  public function cumulate($data)
  {
    $data['user_id'] = request()->user()->id;
    return $data;
  }
}
