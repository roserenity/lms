<?php

namespace App\Http\Requests\Tenants\Auth;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\User;

use Hash;

class LoginRequest extends FormRequest
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
    return [
      'email' => 'required|email|max:255',
      'password' => 'required',
    ];
  }

  public function withValidator($validator)
  {
    $validator->after(function ($validator) {
      $user = User::firstWhere('email',$this->email);
      if ($user) {
        if (!Hash::check($this->password, $user->password)) {
          $validator->errors()->add('password', 'Invalid email or password. Please check your credentials and try again.');
        }
      } else {
        if (!empty($this->email)) {
          $validator->errors()->add('email', 'Invalid email or password. Please check your credentials and try again.');
        }
      }
    });
  }
}
