<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CertificateAttestor;
use App\Models\CertificateLogo;
use App\Models\CertificateSignatory;
use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateCategory;
use App\Services\Tenants\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CertificateTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function index()
    {
      if(request('paginate')){
        $certificate_template = CertificateTemplate::with(
          ['course' => function($query) {
            $query->select(['id', 'title', 'course_code']);
          }, 'uploader' => function($query) {
            $query->select(['id', 'first_name', 'last_name', 'suffix']);
          }])
          ->when(request('search'), function ($query) {
            $query->where('name', 'LIKE', '%' . request('search') . '%');
          })
          ->paginate(request('paginate'));
      } else {
        $certificate_template = CertificateTemplate::with(
          ['course' => function($query) {
            $query->select(['id', 'title', 'course_code']);
          }, 'uploader' => function($query) {
            $query->select(['id', 'first_name', 'last_name', 'suffix']);
          }])
          ->when(request('search'), function ($query) {
            $query->where('name', 'LIKE', '%' . request('search') . '%');
          })
          ->get();
      }
  
      return $this->sendResponse($certificate_template, 'List of all certificate templates.', 200);
    }

    public function show($id)
    {
      
      $certificate_template = CertificateTemplate::with(
        ['course' => function($query) {
          $query->select(['id', 'title', 'course_code']);
        }, 'attestor' => fn($query) => $query->with('image'),
        'signatory' => fn($query) => $query->with('image'),
        'logo' => fn($query) => $query->with('image')])->where('id', $id)->first();
  
      return $this->sendResponse($certificate_template, 'Show certificate template.', 200);

    }

    private function createLogo($certificate_template, $request)
    {
        $logo = CertificateLogo::create([
          'certificate_template_id' => $certificate_template->id,
        ]);

        if ($request->hasFile('logo')) {
          ImageService::uploadCertificateLogo([
            'model_id' => $logo->id,
            'model_type' => 'App\Models\CertificateLogo',
            'image' => $request->file('logo')
          ]);
        }
    }

    private function createSignatory($certificate_template, $request)
    {
        $signatory = CertificateSignatory::create([
          'certificate_template_id' => $certificate_template->id,
          'name' => $request->signatory_name,
          'designation' => $request->signatory_designation,
          'info' => $request->signatory_info ?? '',
        ]);

        if ($request->hasFile('signatory_img')) {
          ImageService::uploadCertificateSignatorySign([
            'model_id' => $signatory->id,
            'model_type' => 'App\Models\CertificateSignatory',
            'image' => $request->file('signatory_img')
          ]);
        }
    }

    private function createAttestor($certificate_template, $request)
    {
        $attestor = CertificateAttestor::create([
          'certificate_template_id' => $certificate_template->id,
          'name' => $request->attestor_name,
          'designation' => $request->attestor_designation,
          'info' => $request->attestor_info ?? '',
        ]);

        if ($request->hasFile('attestor_img')) {
          ImageService::uploadCertificateAttestorySign([
            'model_id' => $attestor->id,
            'model_type' => 'App\Models\CertificateAttestor',
            'image' => $request->file('attestor_img')
          ]);
        }
    }


    public function store(Request $request) 
    {
      
      $rules = [
        'name' => 'required|string',
        'content' => 'required|string',
        'certificate_category' => 'required|exists:certificate_template_categories,id',
        'logo' => 'nullable|mimes:jpg,jpeg,png|file',
        'signatory_name' => 'required_with:signatory_designation,signatory_img|string',
        'signatory_designation' => 'required_with:signatory_name,signatory_img|string',
        'signatory_info' => 'sometimes|string',
        'signatory_img' => 'required_with:signatory_name,signatory_designation|mimes:jpg,jpeg,png|file',
        'attestor_name' => 'required_with:attestor_designation,attestor_img|string',
        'attestor_designation' => 'required_with:attestor_name,attestor_img|string',
        'attestor_info' => 'sometimes|string',
        'attestor_img' => 'required_with:attestor_name,attestor_designation|mimes:jpg,jpeg,png|file',
      ];
      
      $messages = [
        'attestor_name.required_with' => 'Attestor name is required.',
        'attestor_designation.required_with' => 'Attestor designation is required.',
        'attestor_img.required_with' => 'Attestor E-signature is required.',
        'signatory_name.required_with' => 'Signatory name is required.',
        'signatory_designation.required_with' => 'Signatory designation is required.',
        'signatory_img.required_with' => 'Attestor E-signature is required.',
      ];
      
      $request->validate($rules, $messages);

      try {
        DB::beginTransaction();
  
        if($request->default) {
          $certificate_template = CertificateTemplate::where('is_default', 1)->where('certificate_category_id', $request->certificate_category)->first();

          if($certificate_template) {
            $certificate_template->update([
              'is_default' => 0
            ]);
          }
        }

        $certificate_template = CertificateTemplate::create([
          'uploader_id' => $this->authUser()->id,
          'name' => $request->name,
          'content' => $request->content,
          'certificate_category_id' => $request->certificate_category,
          'is_default' => $request->default
        ]);

        $certificate_template->refresh();  
      
        if ($request->logo) {
          $this->createLogo($certificate_template, $request);
        }
      
        if ($request->signatory_name || $request->signatory_designation || $request->signatory_img) {
          $this->createSignatory($certificate_template, $request);
        }

        if ($request->attestor_name || $request->attestor_designation || $request->attestor_img) {
          $this->createAttestor($certificate_template, $request);
        }
        
            
        $default_certificate_template = CertificateTemplate::where('is_default', 1)->where('certificate_category_id', $request->certificate_category)->first();

        if(!$default_certificate_template) {
          $certificate_template->update([
            'is_default' => 1
          ]);
        }

        DB::commit();
        
        return $this->sendResponse([], 'Certificate Template Saved', 200);
      } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Certificate Template Error: ' . $e);
        return $this->sendResponse([], 'Something went wrong', 500);
      }

    }

    public function update($id, Request $request) 
    {
        $request->validate([
          'id' => 'exists:certificate_templates,id',
          'name' => 'required|string',
          'content' => 'required|string',
          'certificate_category' => 'required|exists:certificate_template_categories,id',
        ]);
        
        if($request->logo)
        {
          $request->validate([
            'logo' => 'required|mimes:jpg,jpeg,png|file',
          ]);
        }

        if ($request->filled('signatory_name') || $request->filled('signatory_designation')) {
          $request->validate([
              'signatory_name' => 'required|string',
              'signatory_designation' => 'required|string',
              'signatory_info' => 'sometimes|string',
          ]);
        } 
        
        if ($request->filled('signatory_img')) {
          $request->validate([
              'signatory_name' => 'required|string',
              'signatory_designation' => 'required|string',
              'signatory_info' => 'sometimes|string',
              'signatory_img' => 'required|mimes:jpg,jpeg,png|file',
          ]);
        }

        if ($request->filled('attestor_name') || $request->filled('attestor_designation')) {
          $request->validate([
              'attestor_name' => 'required|string',
              'attestor_designation' => 'required|string',
              'attestor_info' => 'sometimes|string',
          ]);
        }

        if ($request->filled('attestor_img')) {
          $request->validate([
              'attestor_name' => 'required|string',
              'attestor_designation' => 'required|string',
              'attestor_info' => 'sometimes|string',
              'attestor_img' => 'required|mimes:jpg,jpeg,png|file',
          ]);
        }

        try {
            DB::beginTransaction();

            if($request->default) {
              $certificate_template = CertificateTemplate::where('is_default', 1)->where('certificate_category_id', $request->certificate_category)->first();
    
              if($certificate_template) {
                $certificate_template->update([
                  'is_default' => 0
                ]);
              }
            }

            $certificate_template = CertificateTemplate::findOrFail($id);
            $certificate_template->update([
              'name' => $request->name,
              'title' => $request->title,
              'certificate_category_id' => $request->certificate_category,
              'is_default' => $request->default,
              'content' => $request->content
            ]);
            $certificate_template->save();

            // Handle multiple signatories
          
            if($request->logo)
            {
              $request->validate([
                'logo' => 'required|mimes:jpg,jpeg,png|file',
              ]);

              $certificate_logo = CertificateLogo::updateOrCreate([
                'certificate_template_id' => $certificate_template->id,
              ]);
  
              if ($request->hasFile('logo')) {
                  ImageService::uploadCertificateLogo([
                      'model_id' => $certificate_logo->id,
                      'model_type' => 'App\Models\CertificateLogo',
                      'image' => $request->file('logo')
                  ]);
              }
            }

            if ($request->filled('signatory_name')) {
              $signatory = CertificateSignatory::updateOrCreate(
                  [
                      'certificate_template_id' => $certificate_template->id,
                  ],
                  [
                    'name' => $request->signatory_name,
                    'designation' => $request->signatory_designation,
                    'info' => $request->signatory_info,
                  ]
              );

              if ($request->hasFile('signatory_img')) {
                  ImageService::uploadCertificateSignatorySign([
                      'model_id' => $signatory->id,
                      'model_type' => 'App\Models\CertificateSignatory',
                      'image' => $request->file('signatory_img')
                  ]);
              }
            }

            if ($request->filled('attestor_name')) {
                $attestor = CertificateAttestor::updateOrCreate(
                    [
                        'certificate_template_id' => $certificate_template->id,
                    ],
                    [
                      'name' => $request->attestor_name,
                      'designation' => $request->attestor_designation,
                      'info' => $request->attestor_info,
                    ]
                );

                if ($request->hasFile('attestor_img')) {
                    ImageService::uploadCertificateAttestorySign([
                        'model_id' => $attestor->id,
                        'model_type' => 'App\Models\CertificateAttestor',
                        'image' => $request->file('attestor_img')
                    ]);
                }
            }
            
            $default_certificate_template = CertificateTemplate::where('is_default', 1)->where('certificate_category_id', $request->certificate_category)->first();
            
            if (!$default_certificate_template) {
              $cert_first = CertificateTemplate::where('uploader_id', 1)
                  ->where('certificate_category_id', $certificate_template->certificate_category_id)
                  ->first()
                  ?? CertificateTemplate::where('certificate_category_id', $certificate_template->certificate_category_id)->first();
          
              if ($cert_first) {
                  $cert_first->update(['is_default' => 1]);
              }
            }

            DB::commit();

            return $this->sendResponse([], 'Certificate Template Updated', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Certificate Template Update Error: ' . $e);
            return $this->sendResponse([], 'Something went wrong', 500);
        }
    }

    private function duplicateRelatedRecord($model, $oldTemplateId, $newTemplateId)
    {
        $records = $model::where('certificate_template_id', $oldTemplateId)->get(); // Fetch all related records
        foreach ($records as $record) {
            $new_record = $record->replicate()->fill([
                'certificate_template_id' => $newTemplateId
            ]);
            $new_record->save();

            if ($record->image) {
                $new_image = $record->image->replicate()->fill([
                    'imageable_id' => $new_record->id,
                    'imageable_type' => get_class($new_record), // Ensure polymorphic type is correct
                ]);
                $new_image->save();
            }
        }
    }
    
    public function duplicateCertificateTemplate($id)
    {
      try {
        $certificate_template = CertificateTemplate::find($id);

        if (!$certificate_template) {
          return $this->sendResponse([], 'No certificate template found.', 404);
        }

        DB::beginTransaction();

        $certificate_template_count = CertificateTemplate::where('name', 'LIKE', '%' . $certificate_template->name . '.copy%')->count();

        $new_certificate_template = $certificate_template->replicate()->fill([
          'name' => $certificate_template->name . '.copy' . ($certificate_template_count + 1),
          'title' => $certificate_template->title,
          'uploader_id' => $this->authUser()->id,
          'is_default' => 0
        ]);

        $new_certificate_template->save();

        $this->duplicateRelatedRecord(CertificateLogo::class, $certificate_template->id, $new_certificate_template->id);
        $this->duplicateRelatedRecord(CertificateAttestor::class, $certificate_template->id, $new_certificate_template->id);
        $this->duplicateRelatedRecord(CertificateSignatory::class, $certificate_template->id, $new_certificate_template->id);

        DB::commit();

        return $this->sendResponse([], 'Certificate Template Duplicated', 200);
      } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Certificate Template Duplicate Error: ' . $e);
        return $this->sendResponse([], 'Something went wrong', 500);
      }

    }

    public function destroy($id)
    {

      $certificate_template = CertificateTemplate::find($id);

      if (!$certificate_template) {
          return $this->sendResponse([], 'No certificate template found.', 404);
      }

      $default_certificate_template = CertificateTemplate::where('id', '!=', $id)->where('is_default', 1)->where('certificate_category_id', $certificate_template->certificate_category_id)->first();

      if (!$default_certificate_template) {
        $cert_first = CertificateTemplate::where('id', '!=', $id)->where('uploader_id', 1)
            ->where('certificate_category_id', $certificate_template->certificate_category_id)
            ->first()
            ?? CertificateTemplate::where('id', '!=', $id)->where('certificate_category_id', $certificate_template->certificate_category_id)->first();
    
        if ($cert_first) {
            $cert_first->update(['is_default' => 1]);
        }
      }

      $certificate_template->delete();

      $certificate_template->logo()->delete();

      $certificate_template->attestor()->delete();

      $certificate_template->signatory()->delete();

      $certificate_template->certificate_template_has_courses()->delete();


      return $this->sendResponse([], 'You have successfully deleted a certificate template.', 200);
      
    }

    public function getCertificateTemplateCategory()
    {

      $categories = CertificateTemplateCategory::all();

      return $this->sendResponse($categories, 'Return Certificate Template Categories', 200);
    
    }
}