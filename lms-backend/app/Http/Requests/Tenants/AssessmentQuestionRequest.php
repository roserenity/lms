<?php

namespace App\Http\Requests\Tenants;

use App\Models\AssessmentQuestion;
use Illuminate\Foundation\Http\FormRequest;

class AssessmentQuestionRequest extends FormRequest
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
      $assessmentIdRules = 'required';
    } else if (request()->isMethod('put')) {
      $assessmentIdRules = 'nullable';
    }

    return [
      'assessment_id' => $assessmentIdRules,
      'question' => 'nullable',
      'type_of_question' => 'nullable|in:multiple_choice,true_or_false,identification,essay,justified_multiple_choice,modified_true_or_false,sliders,ordering',
      'score' => 'nullable|numeric',
      'require_file_attachment' => 'nullable|boolean',
      'require_support_answer' => 'nullable|boolean',
      'correct_answer' => 'nullable',
      'choices.*' => 'nullable',
      'feedbacks.*' => 'nullable',
      'rubric_id' => 'nullable|numeric',
      'sort_no' => 'required',
    ];
  }

  protected function prepareForValidation()
  {
    $required_support_answer = [
      'justified_multiple_choice',
      'modified_true_or_false'
    ];

    if (in_array($this->type_of_question, $required_support_answer)) {
      $require_support_answer = 1;
    } else {
      $require_support_answer = 0;
    }

    if (request()->isMethod('post')) {
      $assessment_id = $this->input('assessment_id');

      return $this->merge([
        'require_support_answer' => $require_support_answer,
        'sort_no' => AssessmentQuestion::where('assessment_id', $assessment_id)->count() > 0 ? AssessmentQuestion::where('assessment_id', $assessment_id)->max('sort_no') + 1 : 0,
      ]);
    } else {
      return $this->merge([
        'require_support_answer' => $require_support_answer
      ]);
    }
  }

  public function messages()
  {
    return [
      'type_of_question.in' => 'The selected :attribute is invalid. Please select from (multiple_choice, true_or_false, identification, essay, justified_multiple_choice, modified_true_or_false, sliders, ordering).',
    ];
  }
}