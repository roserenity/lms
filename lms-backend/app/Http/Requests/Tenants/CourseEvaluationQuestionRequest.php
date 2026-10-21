<?php

namespace App\Http\Requests\Tenants;

use App\Models\CourseEvaluationQuestion;
use Illuminate\Foundation\Http\FormRequest;

class CourseEvaluationQuestionRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

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
            'course_evaluation_question_id.*' => 'nullable|exists:course_evaluation_questions,id',
            'question' => 'nullable',
            'type_of_question' => 'nullable',
            'is_require_to_answer' => 'nullable',
            'sort_no' => 'required',
            'data' => 'nullable',
        ];
    }

    protected function prepareForValidation()
    {
        $course_evaluation_id = $this->input('course_evaluation_id');

        $this->merge([
            'sort_no' => CourseEvaluationQuestion::where('course_evaluation_id', $course_evaluation_id)->count() > 0 ? CourseEvaluationQuestion::where('course_evaluation_id', $course_evaluation_id)->max('sort_no') + 1 : 0,
        ]);
    }
}
