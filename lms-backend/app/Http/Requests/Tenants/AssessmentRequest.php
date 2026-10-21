<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class AssessmentRequest extends FormRequest
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
      'course_id' => 'required|exists:courses,id',
      'module_id' => 'required|exists:modules,id',
      'uploader_id' => 'sometimes|exists:users,id',
      'is_topic' => 'sometimes|boolean',
      'topic_or_sub_topic_id' => 'sometimes',
      'title' => 'nullable|max:255',
      'type_of_assessment' => 'nullable|in:graded_assessment,practice_assessment,graded_survey,ungraded_survey',
      'description' => 'nullable',
      'require_checking' => 'required|boolean',
      'shuffle_questions' => 'nullable|boolean',
      'shuffle_answers' => 'nullable|boolean',
      'time_limit' => 'nullable|numeric',
      'max_attempt' => 'nullable|numeric',
      'accepted_score' => 'nullable|in:highest,latest,average',
      'passing_score' => 'nullable|numeric|min:1',
      'view_results_by' => 'nullable|in:summary,each_attempt,all_correct_answers',
      'view_results_until' => 'nullable|date',
      'show_each_questions' => 'nullable|boolean',
      'lock_question_after_answering' => 'nullable|boolean',
      'due_date.*' => 'nullable|date'
    ];
  }

  public function messages()
  {
    return [
      'type_of_assessment.in' => 'The selected :attribute is invalid. Please select from (graded_assessment, practice_assessment, graded_survey, ungraded_survey)',
      'accepted_score.in' => 'The selected :attribute is invalid. Please select from (highest,latest,average)',
      'view_results_by.in' => 'The selected :attribute is invalid. Please select from (summary,each_attempt,all_correct_answers)'
    ];
  }

  protected function prepareForValidation()
  {
    $this->merge([
      'uploader_id' => auth()->guard('api')->user()->id,
    ]);
  }
}
