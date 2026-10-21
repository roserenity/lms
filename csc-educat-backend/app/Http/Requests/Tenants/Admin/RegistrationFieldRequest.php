<?php

namespace App\Http\Requests\Tenants\Admin;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\CustomRegistrationField;

use Str;
class RegistrationFieldRequest extends FormRequest
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
            $customRegistrationIdRules = 'required|exists:custom_registration_pages,id';
        } else if (request()->isMethod('put')) {
            $customRegistrationIdRules = 'nullable';
        }

        return [
            'custom_registration_page_id' => $customRegistrationIdRules,
            'input_field_label' => 'required|max:255',
            'input_field_name' => 'required|max:255',
            'input_field_type' => 'required|in:text,email,password,number,date,file,dropdown',
            'is_required' => 'required|boolean',
            'is_multiple' => 'required|boolean',
            'sort_no' => 'required',
            'data' => 'nullable'
        ];
    }

    protected function prepareForValidation()
    {
        $input_field_name = preg_replace("/[^a-zA-Z0-9]/", "", $this->input_field_label);
        $this->merge([
            'input_field_name' => Str::lower($input_field_name),
            'sort_no' => CustomRegistrationField::count() > 0 ? CustomRegistrationField::max('sort_no') + 1 : 0
        ]);
    }
}
