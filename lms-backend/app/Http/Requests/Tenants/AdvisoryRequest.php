<?php

namespace App\Http\Requests\Tenants;

use App\Models\Advisory;
use Illuminate\Foundation\Http\FormRequest;

class AdvisoryRequest extends FormRequest
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
          'image' => 'required|mimes:jpg,jpeg,png|file',
          'uploader_id' => 'required|exists:users,id',
          'sort_no' => 'required|integer'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'sort_no' => Advisory::count() > 0 ? Advisory::max('sort_no') + 1 : 0,
            'uploader_id' => auth()->guard('api')->user()->id
        ]);
    }
}
