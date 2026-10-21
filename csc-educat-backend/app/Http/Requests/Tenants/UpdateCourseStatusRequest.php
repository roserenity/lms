<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

use Str;

class UpdateCourseStatusRequest extends FormRequest
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
      'status' => 'required|in:unpublish,UNPUBLISH,publish,PUBLISH,draft,DRAFT',
    ];
  }

  protected function prepareForValidation()
  {
    $this->merge([
      'status' => Str::upper($this->status),
    ]);
  }

  public function messages()
  {
    return [
      'status.in' => 'The selected status is invalid. Please select from (unpublish, publish, draft).',
    ];
  }
}
