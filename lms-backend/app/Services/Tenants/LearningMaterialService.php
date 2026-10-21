<?php

namespace App\Services\Tenants;

use Illuminate\Support\Facades\Http;
use App\Helpers\StandaloneConfig;

use App\Models\LearningMaterial;
use Illuminate\Http\UploadedFile;
use App\Jobs\DeleteScormTempDirectory;

use App\Services\Tenants\ScormJSService;
use App\Services\Tenants\ScormCloudService;

use Storage;
use Vimeo;
use Zip;
use Str;

use Log;

class LearningMaterialService
{

  public static function uploadFile($data)
  {
    if ($data['file']) {

      try {
        if (config('app.env') == 'local') {
  
          $path = "/course/{$data['course_id']}/{$data['type']}/{$data['model_id']}/learning-material";
  
          $file_path = Storage::disk('public')->put($path, $data['file']);
  
          $url = asset($file_path);
        } else {
          if ($data['file']->extension() != 'mp4') {
            try {

              $path = "course/{$data['course_id']}/{$data['type']}/{$data['model_id']}/learning-material";
  
              $file_path = Storage::disk('s3')->put($path, $data['file']);
    
              $url = Storage::disk('s3')->url($file_path);

            } catch (\Exception $e) {
                Log::error('Error uploading to S3: ' . $e->getMessage());
                throw $e;
            }
          } else {
            try {
              
              $currentDateTime = date("Y-m-d H:i:s");

              $url = Vimeo::upload($data['file'], array(
                'name' => $data['course_id'] . '-' . substr($data['title'], 0, 80) . '-' . $currentDateTime
              ));

              Log::info($url);
              
            } catch (\Exception $e) {
                Log::error('Error uploading to Vimeo: ' . $e);
                throw $e;
            }
          }
        }
      } catch(\Exception $e){
        Log::error('An error occurred during file upload: ' . $e->getMessage());
        return [
          'result' => 'error',
          'errors' => ['file' => 'Something went wrong in uploading your file']
        ];
      }

      LearningMaterial::updateOrCreate(
        ['learnable_id' => $data['model_id'], 'learnable_type' => $data['model_type']],
        [
          'url' => $url,
          'file_type' => $data['file']->extension(),
          'is_downloadable' => $data['is_downloadable']
        ]
      );
    } else if ($data['youtube_link']) {

      $youtube_link = parse_url($data['youtube_link']);

      if($youtube_link['host'] == 'www.youtube.com') {
        parse_str($youtube_link['query'], $params);
        $youtube_video_id = $params['v'];
      } else if($youtube_link['host'] == 'youtu.be') {
        if (strpos($youtube_link['path'], '/') === 0) {
          $youtube_video_id = substr($youtube_link['path'], 1);
        } else {
          $youtube_video_id = $youtube_link['path'];
        }
      } else {
        return [
          'result' => 'error',
          'errors' => ['youtube_link' => 'Invalid Youtube link']
        ];
      }

      $url = "https://youtube.com/embed/{$youtube_video_id}";

      LearningMaterial::updateOrCreate(
        ['learnable_id' => $data['model_id'], 'learnable_type' => $data['model_type']],
        [
          'url' => $url,
          'file_type' => 'youtube',
          'is_downloadable' => $data['is_downloadable']
        ]
      );
    } else if ($data['s3_link']) {
      $url = $data['s3_link'];

      $pattern = '/^https:\/\/([a-zA-Z0-9.-]+)\.s3\.([a-zA-Z0-9-]+)\.amazonaws\.com\/(.*)$/';

      if (!preg_match($pattern, $url, $matches)) {
        return [
          'result' => 'error',
          'errors' => ['s3_link' => 'Invalid S3 link']
        ];
      }

      LearningMaterial::updateOrCreate(
        ['learnable_id' => $data['model_id'], 'learnable_type' => $data['model_type']],
        [
          'url' => $url,
          'file_type' => 's3_link',
          'is_downloadable' => $data['is_downloadable']
        ]
      );
    } else if ($data['gdrive_link']) {
      
      $gdrive_link = parse_url($data['gdrive_link']);

      if($gdrive_link['host'] == 'drive.google.com') {
        if(array_key_exists('query', $gdrive_link) && $gdrive_link['query'] === 'usp=sharing') {
          $paths = explode('/', ltrim($gdrive_link['path']));
          $url = "https://drive.google.com{$paths[0]}/{$paths[1]}/{$paths[2]}/{$paths[3]}/preview";
        } else if (strpos($gdrive_link['path'], 'preview')) {
          $url =  $data['gdrive_link'];
        } else {
          return [
            'result' => 'error',
            'errors' => ['gdrive_link' => 'Invalid Gdrive link']
          ];
        }
      } else {
        return [
          'result' => 'error',
          'errors' => ['gdrive_link' => 'Invalid Gdrive link']
        ];
      }

      LearningMaterial::updateOrCreate(
        ['learnable_id' => $data['model_id'], 'learnable_type' => $data['model_type']],
        [
          'url' => $url,
          'file_type' => 'gdrive_link',
          'is_downloadable' => $data['is_downloadable']
        ]
      );
    } else if ($data['scorm_file']) {

      $title = str_replace(' ', '_', $data['title']);

      $scorm_cloud_course_id = $data['course_id'] . '-' . $data['model_id'] . '-' . $data['type'] . '-' . $title;

      $scormCloudService = app(ScormCloudService::class);

      $url = '';

      try {

        $result = $scormCloudService->uploadCourse($scorm_cloud_course_id, $data['title'], $data['scorm_file'], $data['course_id'], $data['model_id'], $data['model_type']);

        if($result['result'] == 'error') {
          return [
            'result' => 'error',
            'errors' => ['scorm_file' => 'Something went wrong in uploading your file']
          ];
        } else {

          LearningMaterial::updateOrCreate(
            ['learnable_id' => $data['model_id'], 'learnable_type' => $data['model_type']],
            [
              'url' => $url,
              'file_type' => 'scorm_file',
              'is_downloadable' => $data['is_downloadable']
            ]
          );
        }
        // if($result['success']) {
        //   //$job_result = $scormCloudService->getCourseUploadStatus($result['job_id']);

        //   // if($job_result["job_status"] == 'COMPLETE') {
        //   //   $result = $scormCloudService->setCourseTitle($scorm_cloud_course_id, $data['title']);

        //   //   if($result['success']) {
        //   //     $result = $scormCloudService->setCourseConfiguration($scorm_cloud_course_id);
              
        //   //     if($result['success']) {
        //   //       $result = $scormCloudService->createPreviewLink($scorm_cloud_course_id);

        //   //       $url = $result["url"];

        //   //       LearningMaterial::updateOrCreate(
        //   //         ['learnable_id' => $data['model_id'], 'learnable_type' => $data['model_type']],
        //   //         [
        //   //           'url' => $url,
        //   //           'file_type' => 'scorm_file',
        //   //         ]
        //   //       );

        //   //     }

        //   //   }
        //   // } else {
        //   //   return [
        //   //     'result' => 'error',
        //   //     'errors' => ['scorm_file' => 'Something went wrong in uploading your file']
        //   //   ];
        //   // }
        // } else {
        //   return [
        //     'result' => 'error',
        //     'errors' => ['scorm_file' => 'Something went wrong in uploading your file']
        //   ];
        // }
      } catch(\Exception $e){
          return [
            'result' => 'error',
            'errors' => ['scorm_file' => 'Something went wrong in uploading your file']
          ];
      }
      // try {
      //   if (config('app.env') == 'local') {
      //     $tempFolder = Str::uuid();
      //     $path = "course/{$data['course_id']}/{$data['type']}/{$data['model_id']}/learning-material/scorm/" . $tempFolder;
      //     $zip = Zip::open($data['scorm_file']);
      //     $zip->extract(Storage::disk('public')->path('scorm/' . $tempFolder));
      //     foreach ($zip->listFiles() as $file) {
      //       $file_path = Storage::disk('public')->putFileAs($path, new UploadedFile(Storage::disk('public')->path('scorm/' . $tempFolder . '/' . $file), $file), $file);
      //     }
      //     $url = asset($path . '/scormcontent/index.html');
      //     $zip->close();
      //     ScormJSService::uploadIframeJs($path . '/scormcontent/');
      //     $tempDirectory = 'scorm/' . $tempFolder;
      //     dispatch(new DeleteScormTempDirectory($tempDirectory));
      //   } else {
      //     $tempFolder = Str::uuid();
      //     $path = "course/{$data['course_id']}/{$data['type']}/{$data['model_id']}/learning-material/scorm/" . $tempFolder;
      //     $zip = Zip::open($data['scorm_file']);
      //     $zip->extract(Storage::disk('public')->path('scorm/' . $tempFolder));
      //     // Normalize file paths before uploading to S3
      //     foreach ($zip->listFiles() as $file) {
      //       // Adjust for both "/scormcontent/" and "scormcontent/" issues
      //       $normalizedFilePath = ltrim($file, '/');
      //       $localFilePath = Storage::disk('public')->path("scorm/{$tempFolder}/{$normalizedFilePath}");
      //       Storage::disk('s3')->putFileAs($path, new UploadedFile($localFilePath, $normalizedFilePath), $normalizedFilePath);
      //     }

      //     $url = Storage::disk('s3')->url($path . '/scormcontent/index.html');
      //     $zip->close();
      //     ScormJSService::uploadIframeJs($path . '/scormcontent/');
      //     $tempDirectory = 'scorm/' . $tempFolder;
      //     dispatch(new DeleteScormTempDirectory($tempDirectory));
      //   }
      // } catch(\Exception $e){
      //   Log::error('An error occurred during file upload: ' . $e->getMessage());
      //   dispatch(new DeleteScormTempDirectory('scorm/' . $tempFolder));
      //   return [
      //     'result' => 'error',
      //     'errors' => ['scorm_file' => 'Something went wrong in uploading your file']
      //   ];
      // }
    }

    return [
        'result' => 'success',
        'url' => $url
    ];
  }

  public static function addVideoToTenantFolderUri($videoUri)
  {
      $tenantId = StandaloneConfig::tenantId();
      $accessToken = config('vimeo.connections.main.access_token');

      // Step 1: Try to find existing folder
      $folderResponse = Http::withHeaders([
          'Authorization' => "Bearer {$accessToken}"
      ])->get("https://api.vimeo.com/me/projects?query={$tenantId}");

      if (!$folderResponse->ok()) {
          Log::error("Failed to fetch Vimeo folders for tenant: {$tenantId}");
          return false;
      }

      // Step 2: Use existing folder if found, otherwise create it
      if (!empty($folderResponse['data'])) {
          $folderUri = $folderResponse['data'][0]['uri']; // e.g. /users/123/projects/456
      } else {
          $createResponse = Http::withHeaders([
              'Authorization' => "Bearer {$accessToken}"
          ])->post("https://api.vimeo.com/me/projects", [
              'name' => $tenantId
          ]);

          if (!$createResponse->ok()) {
              Log::error("Failed to create Vimeo folder for tenant: {$tenantId}", [
                  'response' => $createResponse->body()
              ]);
              return false;
          }

          $folderUri = $createResponse['uri'];
          Log::info("Created new Vimeo folder for tenant {$tenantId}: {$folderUri}");
      }

      // Step 3: Add video to the folder
      $fullUrl = "https://api.vimeo.com{$folderUri}{$videoUri}";

      $addVideoResponse = Http::withHeaders([
          'Authorization' => "Bearer {$accessToken}"
      ])->put($fullUrl);

      if (!$addVideoResponse->successful()) {
          Log::error("Failed to add video to folder: {$fullUrl}", [
              'response' => $addVideoResponse->body()
          ]);
          return false;
      }

      Log::info("Video successfully added to tenant folder: {$fullUrl}");
      return true;
  }


  public static function addPresetToVideo($video_url)
  {
    $access_token = config('vimeo.connections.main.access_token');
    $preset_id = config('vimeo.connections.main.preset_id');

    $video_exists = Http::withHeaders([
      'Authorization' => "Bearer {$access_token}"
    ])
      ->get("https://api.vimeo.com/me{$video_url}");

    if ($video_exists->ok()) {
      Http::withHeaders([
        'Authorization' => "Bearer {$access_token}"
      ])
        ->put("https://api.vimeo.com{$video_url}/presets/{$preset_id}");
    }
  }

  public static function downloadVideo($video_url)
  {
    $access_token = config('vimeo.connections.main.access_token');
    
    $video_id = basename($video_url); 

    $response = Http::withHeaders([
        'Authorization' => "Bearer {$access_token}"
    ])->get("https://api.vimeo.com/videos/{$video_id}");

    if ($response->ok()) {
        $video_data = $response->json();
        
        if (isset($video_data['download'])) {
            $downloadLinks = $video_data['download'];

            $highest_quality_link = null;
            foreach ($downloadLinks as $file) {
                if ($file['quality'] === 'hd') {  // You can select 'hd' or 'sd' based on your preference
                    $highest_quality_link = $file['link'];
                    break;
                }
            }

            if ($highest_quality_link) {
                return [
                    'result' => 'success',
                    'downloadable_url' => $highest_quality_link
                ];
            }
            
            return [
                'result' => 'success',
                'downloadable_url' => $video_data['download'][0]['link']
            ];
        } else {
          return [
            'result' => 'failed',
            'message' => "Video does not have download links."
          ];
        }
    } else {
        return [
          'result' => 'failed',
          'message' => 'Failed to fetch video details.'
        ];
    }
  }
}
