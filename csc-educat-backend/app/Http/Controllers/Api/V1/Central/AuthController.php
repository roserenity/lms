<?php

namespace App\Http\Controllers\Api\V1\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\LoginRequest;
use App\Models\Central\SuperAdmin;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
  // Super admin authentication
  public function __construct()
  {
    $this->middleware('auth.super_admin')->except('login');
  }

  public function user() {
    return auth()->guard('super-admin-api')->user();
  }

  public function login(LoginRequest $request)
  {
    $user = SuperAdmin::firstWhere('email', $request->email);

    $user['access_token'] = $user->createToken('central', [])->accessToken;

    return $this->sendResponse($user, 'Successfully generate an access token.', 201);
  }

  public function logout()
  {
    $access_token = auth()->guard('super-admin-api')->user()->token();
    $access_token->revoke();
    return $this->sendResponse([], 'Successfully logout!', 200);
  }

}
