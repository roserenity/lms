<?php

namespace App\Http\Requests\Tenants\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BannedWordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if (request()->isMethod('post')) {
            $bannedWordIdRules = 'required|exists:banned_words,id';
        } else if (request()->isMethod('put')) {
            $bannedWordIdRules = 'nullable';
        }

        return [
            'id' => $bannedWordIdRules,
            'word' => 'required|unique:banned_words,word|max:255',
        ];
    }
}
