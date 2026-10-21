<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends FormRequest
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

  //Create Tenant
  public function rules()
  {
    return [
      'tenant_name' => 'required|max:255',
      'organization' => 'required|in:general_education,corporate,ngo',
      'domain_name' => 'required|max:255',
      'sub_domain' => 'required|unique:tenants,api_endpoint|max:255',
      'status' => 'required|in:active,inactive',
      'tenant_description' => 'required',
      'contact_person' => 'required|max:255',
      'email' => 'required|email|unique:tenant_infos,email|max:255',
      'mobile_no' => 'required',
      'tel_no' => 'required',
      'images.*' => 'nullable|mimes:jpg,jpeg,png,ico|file|max:5000',
      'image_key.*' => 'nullable',
      'navigation_background_color' => 'nullable',
      'navigation_banner_color' => 'nullable',
      'navigation_link_color' => 'nullable',
      'navigation_text_icon_color' => 'nullable',
      'navigation_link_inactive_color' => 'nullable',
      'sidebar_color' => 'nullable',
      'mini_navigation_background_color' => 'nullable',
      'mini_navigation_link_color' => 'nullable',
      'mini_navigation_icon_color' => 'nullable',
      'mini_navigation_inactive_link_color' => 'nullable',
      'primary_buttons_color' => 'nullable',
    ];
  }

  public function messages()
  {
    return [
      'organization.in' => 'The selected organization is invalid. Please select from (general_education, corporate, ngo).',
      'status.in' => 'The selected status is invalid. Please select from (active or inactive).',
    ];
  }
}
