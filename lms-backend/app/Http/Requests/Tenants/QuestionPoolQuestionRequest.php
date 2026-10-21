<?php

namespace App\Http\Requests\Tenants;

use App\Models\QuestionPoolQuestion;

use Illuminate\Foundation\Http\FormRequest;

class QuestionPoolQuestionRequest extends FormRequest
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
        if (request()->isMethod('put')) {
          $questionIdRules = 'required';
        } else if (request()->isMethod('post')) {
          $questionIdRules = 'nullable';
        }
    
        return [
          'id' => $questionIdRules,
          'name' => 'required|max:255',
          'type_of_question_pool' => 'required',
          'question_pool_id' => 'required|exists:question_pools,id',
          'question' => 'nullable',
          'type_of_question' => 'nullable|in:multiple_choice,true_or_false,identification,essay,justified_multiple_choice,modified_true_or_false,sliders,ordering,linear_scale,multiple_choice_grid,checkbox_grid,date,time,dropdown,file_upload,checkbox,paragraph',
          'score' => 'nullable|numeric',
          'require_file_attachment' => 'nullable|boolean',
          'require_support_answer' => 'nullable|boolean',
          'is_require_to_answer' => 'nullable|boolean',
          'rubric_id' => 'nullable|numeric',
          'sort_no' => 'required',
          'data' => 'nullable',
          'uploader_id' => 'required|exists:users,id'
        ];
    }

    protected function prepareForValidation()
    {
        $question_pool_id = $this->input('question_pool_id');

        $this->merge([
            'sort_no' => QuestionPoolQuestion::where('question_pool_id', $question_pool_id)->count() > 0 ? QuestionPoolQuestion::where('question_pool_id', $question_pool_id)->max('sort_no') + 1 : 0,
            'uploader_id' => auth()->guard('api')->user()->id
        ]);
    }
}
