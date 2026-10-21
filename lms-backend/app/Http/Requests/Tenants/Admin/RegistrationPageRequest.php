<?php

namespace App\Http\Requests\Tenants\Admin;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\CustomRegistrationPage;

class RegistrationPageRequest extends FormRequest
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
            'title' => 'required|unique:custom_registration_pages,title|max:255',
            'sort_no' => 'required'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'sort_no' => CustomRegistrationPage::count() > 0 ? CustomRegistrationPage::max('sort_no') + 1 : 0,
        ]);
    }
}
