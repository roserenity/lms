<?php

namespace App\Http\Requests\Tenants\Auth;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rules\Password;
use Hash;
class UpdatePasswordRequest extends FormRequest
{
  /**
  * Determine if the user is authorized to make this request.
  *
  * @return bool
  */
  public function authorize()
  {
    return true;
  }

  /**
  * Get the validation rules that apply to the request.
  *
  * @return array
  */
  public function rules()
  {
    $passwordRules = [
      'required',
      'confirmed',
      Password::min(8)
      ->letters()
      ->mixedCase()
      ->numbers()
      ->uncompromised()
    ];

    return [
      'current_password' => 'required|sometimes|current_password:api',
      'password' => $passwordRules,
      'password_confirmation' => 'required'
    ];
  }

  public function withValidator($validator)
  {
    $validator->after(function ($validator) {
        if (Hash::check($this->password, auth()->guard('api')->user()->password)) {
          $validator->errors()->add('password', 'The new password cannot be the same as your current password');
        }
    });
  }
}
