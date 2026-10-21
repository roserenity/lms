<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\AdvisoryRequest;
use App\Http\Requests\Tenants\UpdateAdvisoryRequest;
use App\Models\Advisory;
use Illuminate\Http\Request;
use App\Services\Tenants\ImageService;

class AdvisoryController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth.admin');
    }

    public function index()
    {
      $advisories = Advisory::select(['id', 'created_at'])->whereHas('image')->with('image')->where('active', Advisory::IS_ACTIVE)->orderBy('sort_no')->get();

      return $this->sendResponse($advisories, 'List of all advisories.', 200);
    }

    public function store(AdvisoryRequest $request)
    {
      $advisory = Advisory::create($request->validated());

      ImageService::uploadAdvisoryImage([
        'model_id' => $advisory->id,
        'model_type' => 'App\Models\Advisory',
        'image' => $request->file('image')
      ]);
  
      return $this->sendResponse([], 'You have successfully uploaded a new advisory', 201);
    }

    public function update(Request $request, $id)
    {

      $request->validate([
        'id' => 'required|exists:advisories,id',
        'active' => 'required|in:0,1',
        'sort_no' => 'required'
      ]);

      $advisory = Advisory::find($id);
  
      if (!$advisory) {
        return $this->sendResponse([], 'No advisory found.', 404);
      }
  
      $advisory->update($request->validated())->except('id');
  
      return $this->sendResponse([], 'You have successfully updated an advisory.', 200);
    }

    public function destroy($id)
    {
      $advisory = Advisory::find($id);
  
      if (!$advisory) {
        return $this->sendResponse([], 'No advisory found.', 404);
      }

      $advisory->delete();
      
      return $this->sendResponse([], 'You have successfully deleted an advisory.', 200);
    }
}
