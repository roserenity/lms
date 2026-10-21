<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class ForumPostRequest extends FormRequest
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
          $idRules = 'sometimes';
        } else if (request()->isMethod('put')) {
          $idRules = 'required|exists:forum_posts,id';
        }

        return [
            'id' => $idRules,
            'course_id' => 'nullable|sometimes|exists:courses,id',
            'topic_category' => 'sometimes',
            'content' => 'required',
            'attachments.*' => 'nullable|mimes:jpg,jpeg,png,txt,doc,docx,ppt,pptx,pdf',
            'status' => 'nullable|in:APPROVED,DECLINED,PENDING,REMOVED',
        ];
    }

    protected function prepareForValidation()
    {
        return [
            'user_id' => auth()->guard('api')->user()->id
        ];
    }
}
