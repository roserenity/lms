<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class PrivateMessageRequest extends FormRequest
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
            'private_chat_id' => 'nullable',
            'message_body' => 'required_if:attachments.*,null',
            'attachments.*' => 'nullable|mimes:jpg,jpeg,png,txt,doc,docx,ppt,pptx,pdf',
            'to_user_id' => 'required',
            'from_user_id' => 'required'
        ];
    }
}
