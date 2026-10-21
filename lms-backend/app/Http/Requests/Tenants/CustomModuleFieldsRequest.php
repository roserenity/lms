<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\CustomModuleField;

class CustomModuleFieldsRequest extends FormRequest
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
            'id' => 'sometimes|exists:custom_module_fields,id',
            'input_field_label' => 'required|string|max:255',
            'input_field_name' => 'required|string|max:255',
            'input_field_type' => 'required|in:text_area,text_field,number,calendar,dropdown',
            'is_multiple' => 'required|boolean',
            'sort_no' => 'required',
            'uploader_id' => 'required|exists:users,id',
            'data' => 'nullable'
        ];
    }

    protected function prepareForValidation()
    {
      $this->merge([
        'sort_no' => CustomModuleField::count() > 0 ? CustomModuleField::max('sort_no') + 1 : 0,
        'uploader_id' => auth()->guard('api')->check() ? auth()->guard('api')->user()->id : null,
      ]);
    }
}
