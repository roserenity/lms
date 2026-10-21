<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class UserAccountStatusRequest extends FormRequest
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
      'user_id' => 'required',
      'status' => 'required|in:active,inactive'
    ];
  }

  public function messages()
  {
    return [
      'status.in' => 'The status is invalid. Please select from (active or inactive).',
    ];
  }
}
