<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class ModulePrerequisiteRequest extends FormRequest
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
      'module_id' => 'required',
      'prerequisite_module_id.*' => 'sometimes',
    ];
  }

  protected function prepareForValidation()
  {
    $this->merge([
      'uploader_id' => auth()->guard('api')->user()->id,
    ]);
  }
}
