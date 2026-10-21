<?php

namespace App\Http\Requests\Tenants\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

use App\Models\User;

class RegisterRequest extends FormRequest
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
        $passwordRules = [
            'required',
            Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->uncompromised()
        ];

        $emailRules = [
            'required',
            'email',
            'max:255',
            Rule::unique('users')->whereNull('deleted_at')
        ];

        return [
            'first_name' => 'required|max:255',
            'middle_initial' => 'sometimes|max:10',
            'middle_name' => 'sometimes|max:255',
            'last_name' => 'required|max:255',
            'suffix' => 'sometimes|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => $passwordRules,
            'class_category_id' => 'sometimes|exists:class_categories,id',
            'role' => 'required',
            'is_first_login' => 'nullable|boolean',
            'data' => 'sometimes'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'role' => User::ROLE_USER,
            'is_first_login' => false
        ]);
    }
}
