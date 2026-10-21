<?php

namespace App\Http\Requests\Tenants\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordFormRequest extends FormRequest
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
      'email' => 'required|email|exists:users,email|max:255',
      'url' => 'required|url'
    ];
  }

  public function messages()
  {
    return [
      'email.exists' => 'This email do not match on our records.',
    ];
  }
}
