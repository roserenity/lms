<?php

namespace App\Http\Requests\Tenants;

use App\Rules\NoBannedWords;
use Illuminate\Foundation\Http\FormRequest;

class ForumPostCommentRequest extends FormRequest
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
            'parent_id' => 'nullable|sometimes',
            'comment' => ['required', new NoBannedWords],
            'commentable_id' => 'required',
            'attachments.*' => 'nullable|mimes:jpg,jpeg,png,txt,doc,docx,ppt,pptx,pdf',
        ];
    }

    protected function prepareForValidation()
    {
        return [
            'user_id' => auth()->guard('api')->user()->id
        ];
    }
    
}
