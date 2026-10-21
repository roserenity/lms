<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Module;

class ModuleRequest extends FormRequest
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
      $courseIdRules = 'required';
    } else if (request()->isMethod('put')) {
      $courseIdRules = 'sometimes';
    }

    return [
      'course_id' => $courseIdRules,
      'title' => 'sometimes|max:255',
      'description' => 'sometimes',
      'validity' => 'sometimes',
      'available_from' => 'sometimes|date',
      'available_to' => 'sometimes|date',
      'sort_no' => 'required',
      'uploader_id' => 'required',
      'contact_person' => 'sometimes',
      'version' => 'sometimes',
      'language' => 'sometimes',
      'data' => 'sometimes',
    ];
  }

  protected function prepareForValidation()
  {
    $this->merge([
      'sort_no' => Module::count() > 0 ? Module::max('sort_no') + 1 : 0,
      'uploader_id' => auth()->guard('api')->user()->id
    ]);
  }
}
