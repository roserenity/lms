<?php

namespace App\Http\Requests\Tenants;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
            'reference_id' => 'nullable',
            'official_receipt_no' => 'nullable',
            'course_id.*' => 'required|exists:courses,id',
            'email' => 'required|email|exists:users,email',
            'enroll_by_id' => 'required',
            // 'image' => 'required|mimes:jpg,jpeg,png,pdf',
            'email' => [
                'required',
                'email',
                Rule::exists('users')->where(function ($query) {
                    return $query->where('email', $this->email);
                }),
            ],
            'type_of_participant' => 'required|in:internal_employee,regular_payee,regular_payee_hrm',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'enroll_by_id' => auth()->guard('api')->user()->id,
        ]);
    }
}
