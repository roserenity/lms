<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

trait LoginTrait
{
  public function obstruct($user, $req)
  {
    if(empty($user)) {
      throw ValidationException::withMessages([
        'email' => 'Email not found!'
      ]);
    }

    if(!Hash::check($req->password, $user->password)) {
      throw ValidationException::withMessages([
        'password' => 'Password not match!'
      ]);
    }
  }
}
