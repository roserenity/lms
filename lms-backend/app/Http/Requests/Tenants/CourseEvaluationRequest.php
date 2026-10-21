<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class CourseEvaluationRequest extends FormRequest
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
            'title' => 'required|max:255',
            'description' => 'nullable',
            'is_shuffle_questions' => 'boolean',
            'is_send_feedback_to_user' => 'boolean',
            'submission_limit' => 'nullable|numeric',
            'is_allow_to_review' => 'boolean',
            'validity' => 'sometimes',
            'uploader_id' => 'required',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'uploader_id' => auth()->guard('api')->user()->id
        ]);
    }
}
