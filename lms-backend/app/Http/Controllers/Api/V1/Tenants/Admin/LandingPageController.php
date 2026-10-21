<?php

namespace App\Http\Controllers\Api\V1\Tenants\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\LandingPageAssetRequest;
use App\Models\LandingPageAsset;
use App\Services\Tenants\ImageService;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth.admin');
    }

    public function showLandingPageBackgrounds()
    {
      $backgrounds = LandingPageAsset::select(['id', 'asset_key'])->whereHas('image')->with('image')->where('asset_key', LandingPageAsset::ASSET_BG)->orderBy('sort_no')->get();

      return $this->sendResponse($backgrounds, 'List of all landing page backgrounds.', 200);
    }

    
    public function createLandingPageBackground(LandingPageAssetRequest $request)
    {
        $asset = LandingPageAsset::first();
        if ($asset) {
            $asset->delete();
        }

        $asset = LandingPageAsset::create($request->validated());

        ImageService::uploadLandingPageAsset([
            'model_id' => $asset->id,
            'model_type' => LandingPageAsset::class,
            'image' => $request->file('image')
        ]);

        return $this->sendResponse([], 'You have successfully uploaded a new landing page background', 201);
    }

    public function deleteLandingPageBackground($id)
    {
      $asset = LandingPageAsset::find($id);
  
      if (!$asset) {
        return $this->sendResponse([], 'No asset found.', 404);
      }

      $asset->delete();
      
      return $this->sendResponse([], 'You have successfully deleted an asset.', 200);
    }

}
