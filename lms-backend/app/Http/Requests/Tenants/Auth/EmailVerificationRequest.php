<?php

namespace App\Http\Requests\Tenants\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
  /**
  * Determine if the user is authorized to make this request.
  *
  * @return bool
  */
  public function authorize()
  {
    if (! hash_equals((string) $this->route('id'),
    (string) auth()->guard('api')->user()->getKey())) {
      return false;
    }

    if (! hash_equals((string) $this->route('hash'),
    sha1(auth()->guard('api')->user()->getEmailForVerification()))) {
      return false;
    }

    return true;
  }

  /**
  * Get the validation rules that apply to the request.
  *
  * @return array
  */
  public function rules()
  {
    return [
      //
    ];
  }

  public function fulfill()
  {
    if (! auth()->guard('api')->user()->hasVerifiedEmail()) {
      auth()->guard('api')->user()->markEmailAsVerified();

      event(new Verified(auth()->guard('api')->user()));
    }
  }
}
