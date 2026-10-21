<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ModuleEvaluationQuestion;

class ModuleEvaluationQuestionRequest extends FormRequest
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
            'module_evaluation_id' => 'required|exists:module_evaluations,id',
            'module_evaluation_question_id.*' => 'nullable|exists:module_evaluation_questions,id',
            'question' => 'nullable',
            'type_of_question' => 'nullable',
            'is_require_to_answer' => 'nullable',
            'sort_no' => 'required',
            'data' => 'nullable',
        ];
    }

    protected function prepareForValidation()
    {
        $module_evaluation_id = $this->input('module_evaluation_id');

        $this->merge([
            'sort_no' => ModuleEvaluationQuestion::where('module_evaluation_id', $module_evaluation_id)->count() > 0 ? ModuleEvaluationQuestion::where('module_evaluation_id', $module_evaluation_id)->max('sort_no') + 1 : 0,
        ]);
    }
}
