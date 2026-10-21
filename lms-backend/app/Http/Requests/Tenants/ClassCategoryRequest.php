<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class ClassCategoryRequest extends FormRequest
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
      $classCodeRules = 'required|unique:class_categories,class_code|max:255';
    } else if (request()->isMethod('put')) {
      $classCodeRules = 'nullable';
    }

    return [
      'class_code' => $classCodeRules,
      'class_name' => 'required|max:255',
    ];
  }
}
