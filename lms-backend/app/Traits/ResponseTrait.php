<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait ResponseTrait
{
  public function ok($data)
  {
    return response()->json($data, Response::HTTP_OK);
  }

  public function nocontent()
  {
    return response()->json(Response::HTTP_NO_CONTENT);
  }

  public function created()
  {
    return response()->json(Response::HTTP_CREATED);
  }

  public function forbidden()
  {
    return response()->json(Response::HTTP_FORBIDDEN);
  }
}
