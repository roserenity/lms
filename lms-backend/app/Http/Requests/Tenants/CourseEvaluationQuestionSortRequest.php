<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class CourseEvaluationQuestionSortRequest extends FormRequest
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
        'course_evaluation_id' => 'required|exists:course_evaluations,id',
        'course_evaluation_question_id' => 'required|array'
    ];
  }
}
