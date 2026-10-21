<?php

namespace App\Services\Tenants;

use App\Models\Image;

use InterventionImage;
use Storage;

class ImageService
{

  public static function uploadCourseImage($data)
  {
    $image_resize = InterventionImage::make($data['image'])->resize(520, 520)->encode('jpg', 80);

    $path = "course/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::updateOrCreate(
      ['imageable_id' => $data['model_id'], 'imageable_type' => $data['model_type']],
      [
        'url' => $url,
      ]
    );

    return $url;
  }

  public static function uploadGroupChatImage($data)
  {
    $image_resize = InterventionImage::make($data['image'])->resize(520, 520)->encode('jpg', 80);

    $path = "group-chat/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::updateOrCreate(
      ['imageable_id' => $data['model_id'], 'imageable_type' => $data['model_type']],
      [
        'url' => $url,
      ]
    );

    return $url;
  }

  public static function uploadUserImage($data)
  {
    $image_resize = InterventionImage::make($data['image'])->resize(520, 520)->encode('jpg', 80);

    $path = "user/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::updateOrCreate(
      ['imageable_id' => $data['model_id'], 'imageable_type' => $data['model_type']],
      [
        'url' => $url,
      ]
    );

    return $url;
  }

  public static function uploadTicketFileAttachment($data)
  {
    $image_resize = InterventionImage::make($data['file_attachment'])->encode('jpg', 80);

    $path = "ticket/{$data['model_id']}/{$data['file_attachment']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadTicketReplyFileAttachment($data)
  {
    $image_resize = InterventionImage::make($data['file_attachment'])->encode('jpg', 80);

    $path = "ticket/{$data['model_id']}/{$data['file_attachment']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadOfficialReceipt($data)
  {
    $file_type = strtolower($data['image']->getClientOriginalExtension());

    if($file_type === 'pdf') {
      $image_resize = file_get_contents($data['image']);
    } else {
      $image_resize = InterventionImage::make($data['image'])->resize(520, 520)->encode('jpg', 80);
    }

    $path = "invoice/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadCourseEvaluationQuestionImage($data)
  {
    $image_resize = InterventionImage::make($data['image'])->resize(520, 520)->encode('jpg', 80);

    $path = "course_evaluation_question/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadQuestionPoolQuestionImage($data)
  {
    $image_resize = InterventionImage::make($data['image'])->resize(520, 520)->encode('jpg', 80);

    $path = "question_pool_question/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadModuleEvaluationQuestionImage($data)
  {
    $image_resize = InterventionImage::make($data['image'])->resize(520, 520)->encode('jpg', 80);

    $path = "module_evaluation_question/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadCourseEvaluationAnswerFileAttachment($data)
  {
    $path = "student_course_evaluation_answer/{$data['model_id']}/{$data['file_attachment']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, file_get_contents($data['file_attachment']));

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, file_get_contents($data['file_attachment']));

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadModuleEvaluationAnswerFileAttachment($data)
  {
    $path = "student_module_evaluation_answer/{$data['model_id']}/{$data['file_attachment']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, file_get_contents($data['file_attachment']));

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, file_get_contents($data['file_attachment']));

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }
  
  public static function uploadAdvisoryImage($data)
  {
    $image_resize = InterventionImage::make($data['image'])->encode('jpg', 80);

    $path = "advisory/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, file_get_contents($data['image']));
      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadLandingPageAsset($data)
  {
    $image_resize = InterventionImage::make($data['image'])->encode('jpg', 80);

    $path = "landing-page-asset/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, file_get_contents($data['image']));
      Storage::disk('public')->put($path, $image_resize);

      $url = asset($path);
    }

    Image::create([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadCertificateLogo($data)
  {
    $path = "certificate-logo/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, file_get_contents($data['image']));

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, file_get_contents($data['image']));

      $url = asset($path);
    }

    Image::updateOrCreate([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
    ],[
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadCertificateAttestorySign($data)
  {
    $path = "certificate-attestory/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, file_get_contents($data['image']));

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, file_get_contents($data['image']));

      $url = asset($path);
    }

    Image::updateOrCreate([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
    ],[
      'url' => $url
    ]);

    return $url;
  }

  public static function uploadCertificateSignatorySign($data)
  {
    $path = "certificate-signatory/{$data['model_id']}/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, file_get_contents($data['image']));

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, file_get_contents($data['image']));

      $url = asset($path);
    }

    Image::updateOrCreate([
      'imageable_id' => $data['model_id'],
      'imageable_type' => $data['model_type'],
    ],[
      'url' => $url
    ]);

    return $url;
  }
}
