<?php

namespace App\Services\Tenants;

use App\Helpers\Helper;
use App\Traits\S3Bucket;
use Intervention\Image\Facades\Image;

class ContentService
{
  use S3Bucket;

  public function image($data, $file)
  {
    $folder = "{$data->folder}/image/";
    $filename = time() . '.' . $file->getClientOriginalExtension();
    $this->deleteImageIfExists($data, $folder);
    $imageToUpload = $this->resizeImage($file);
    $directory = $folder.$filename;

    $this->uploadImage(
      $imageToUpload,
      Helper::bucketName(),
      $directory
    );

    return $filename;
  }

  public function resizeImage($file)
  {
    $data = Image::make($file->getRealPath());
    $data->resize(512, 512, function ($const) {
      $const->aspectRatio();
    });
    return $data->stream();
  }

  public function deleteImageIfExists($data, $dir) :void
  {
    if ($data->image) {
      $check = $dir . $data->image->image;
      if ($this->getObject(Helper::bucketName(), $check)) $this->deleteObject(Helper::bucketName(), $check);
    }
  }

  public function material($data, $material)
  {
    $original = $material->getClientOriginalName();
    $filename = tenant('id').'-'.date('Y-m-d').'-'.time();

  }
}
