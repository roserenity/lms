<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

  public function authUser()
  {
    return auth()->guard('api')->user();
  }

  public function sendResponse($result, $message, $code)
  {
    $response = [
      'message' => $message,
      'data' => $result,
    ];

    return response()->json($response, $code);
  }

}
