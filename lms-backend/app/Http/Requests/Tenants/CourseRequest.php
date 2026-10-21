<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
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
      'title' => 'required|max:255',
      'description' => 'nullable',
      'price' => 'nullable|numeric',
      'is_free' => 'nullable|boolean',
      'training_hours' => 'required|numeric|min:1',
      'credit_training_hours' => 'required|numeric|min:1',
      'training_credits' => 'required|max:255',
      'uploader_id' => 'required',
    ];
  }

  protected function prepareForValidation()
  {
    $this->merge([
      'uploader_id' => auth()->guard('api')->user()->id,
    ]);
  }
}
