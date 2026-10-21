<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class GroupChatRequest extends FormRequest
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
            'group_chat_name' => 'required',
            'user_id' => 'required|exists:users,id',
            'image_file' => 'nullable|mimes:jpg,jpeg,png',
            'course_id' => 'sometimes|exists:courses,id'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->guard('api')->user()->id
        ]);
    }
}
