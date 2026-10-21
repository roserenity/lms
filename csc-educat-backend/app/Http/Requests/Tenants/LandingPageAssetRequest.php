<?php

namespace App\Http\Requests\Tenants;

use App\Models\LandingPageAsset;
use Illuminate\Foundation\Http\FormRequest;

class LandingPageAssetRequest extends FormRequest
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
            'asset_key' => 'required|in:background',
            'sort_no' => 'required|integer'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'sort_no' => LandingPageAsset::count() > 0 ? LandingPageAsset::max('sort_no') + 1 : 0,
            'uploader_id' => auth()->guard('api')->user()->id
        ]);
    }
}
