<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class GroupActivityRequest extends FormRequest
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
          'title' => 'required|max:255',
          'uploader_id' => 'required|exists:users,id',
          'group_activity_tool' => 'sometimes|in:GDOC,GSHEET,GSLIDE',
          'description' => 'sometimes|max:255',
          'due_date' => 'nullable',
          'is_topic' => 'sometimes|boolean',
          'topic_or_sub_topic_id' => 'sometimes',
          'group_count' => 'required',
          'group_activity_questions.*' => 'sometimes'
        ];
    }
    

    protected function prepareForValidation()
    {
        $this->merge([
            'uploader_id' => auth()->guard('api')->user()->id
        ]);
    }

}
