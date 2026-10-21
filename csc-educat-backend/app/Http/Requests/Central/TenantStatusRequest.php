<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class TenantStatusRequest extends FormRequest
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
      'tenant_id' => 'required|exists:tenants,id|max:255',
      'tenant_status' => 'required|in:active,inactive',
    ];
  }

  public function messages()
  {
    return [
      'tenant_id.exists' => 'The tenant id do not match on our records.',
      'tenant_status.in' => 'The tenant status is invalid. Please select from (active or inactive).',
    ];
  }
}
