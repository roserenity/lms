<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

use Hash;
use Str;

class UserAccountRequest extends FormRequest
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
    if (request()->isMethod('post')) {
      $emailRules = 'required|email|unique:users,email,NULL,id,deleted_at,NULL|max:255';
    } else if (request()->isMethod('put')) {
      $emailRules = 'nullable';
    }

    return [
      'first_name' => 'required|max:255',
      'middle_initial' => 'sometimes|max:10',
      'middle_name' => 'sometimes|max:255',
      'last_name' => 'required|max:255',
      'suffix' => 'sometimes|max:255',
      'role' => 'in:user,USER,instructor,INSTRUCTOR,admin,ADMIN',
      'email' => $emailRules,
      'password' => 'min:8',
      'course_id' => 'exclude_if:role,INSTRUCTOR|nullable|exists:courses,id'
    ];
  }

  protected function prepareForValidation()
  {
    $this->merge([
      'role' => Str::upper($this->role),
      'password' => Str::random(8),
    ]);
  }

  public function messages()
  {
    return [
      'role.in' => 'The selected role is invalid. Please select from (user or instructor).',
      'course_id.exists' => 'The selected :attribute does not exist on our records.'
    ];
  }
}
