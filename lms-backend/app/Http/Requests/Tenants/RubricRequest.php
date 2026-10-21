<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class RubricRequest extends FormRequest
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
      $rubricCriteriaIdRules = 'nullable';
      $rubricRatingIdRules = 'nullable';
      $typeOfRubricRules = 'required|in:custom,essay,output_based';
    } else if (request()->isMethod('put')) {
      $rubricCriteriaIdRules = 'required';
      $rubricRatingIdRules = 'required';
      $typeOfRubricRules = 'nullable';
    }

    return [
      //Rubric
      'title' => 'required|max:255',
      'type_of_rubric' => $typeOfRubricRules,
      'description' => 'required|max:255',
      'uploader_id' => 'required',
      //Rubric criteria
      // 'rubric_criteria_id.*' => $rubricCriteriaIdRules,
      // 'criteria_title.*' => 'required|max:255',
      // 'criteria_score.*' => 'required|numeric',
      //Rubric rating
      // 'rubric_rating_id.*' => $rubricRatingIdRules,
      // 'rating_number.*' => 'required|numeric',
      // 'rating_description.*' => 'required|max:255'
    ];
  }

  protected function prepareForValidation()
  {
    return $this->merge([
      'uploader_id' => auth()->guard('api')->user()->id
    ]);
  }

  public function messages()
  {
    return [
      'type_of_rubric.in' => 'The selected :attribute is invalid. Please select from (custom, essay, output_based).',
    ];
  }
}
