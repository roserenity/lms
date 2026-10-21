<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateCategory;
use Illuminate\Http\Request;

class CertificateTemplateCategoryController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth.admin');
    }
  
    public function index()
    {

      $certificate_template_categories = CertificateTemplateCategory::
          when(request('search'), function ($query) {
            $query->where('name', 'LIKE', '%' . request('search') . '%');
          })->paginate(request('paginate'));
  
      return $this->sendResponse($certificate_template_categories, 'List of all certificate template categories.', 200);
    
    }

    public function store(Request $request)
    {
      $request->validate([
        'name' => 'required|string'
      ]);

      CertificateTemplateCategory::create([
        'name' => $request->name,
        'uploader_id' => $this->authUser()->id
      ]);
      
      return $this->sendResponse([], 'You have successfully created a new certificate category.', 201);
    }

    public function destroy($id)
    {
      $certificate_category = CertificateTemplateCategory::find($id);

      $default_certificate_template = CertificateTemplate::where('certificate_category_id', $id)->where('is_default', 1)->first();

      if($default_certificate_template){
        $default_certificate_template->update([
          'is_default' => 0
        ]);
      }

      $certificate_category->delete();
  
      return $this->sendResponse([], 'certificate category deleted.', 200);
    }
}
