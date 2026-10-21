<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class GroupChatMessageRequest extends FormRequest
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
            'group_chat_id' => 'required|exists:group_chats,id',
            'message_body' => 'required_if:attachments.*,null',
            'attachments.*' => 'nullable|mimes:jpg,jpeg,png,txt,doc,docx,ppt,pptx,pdf',
            'synced_users' => 'required',
            'sender_id' => 'required|exists:users,id'
        ];
    }

    protected function prepareForValidation()
    {
        return [
            'sender_id' => auth()->guard('api')->user()->id,
            'synced_users' => false
        ];
    }
}
