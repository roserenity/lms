<?php

namespace App\Http\Requests\Tenants;

use App\Models\RegistrationTable;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationTableRequest extends FormRequest
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
            //
        ];
    }

    protected function prepareForValidation()
    {
      $this->merge([
        'sort' => RegistrationTable::count() > 0 ? RegistrationTable::max('sort') + 1 : 0,
      ]);
    }
}
