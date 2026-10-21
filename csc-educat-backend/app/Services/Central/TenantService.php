<?php

namespace App\Services\Central;

use App\Models\Central\Tenant;
use App\Models\Central\Image;
use App\Models\User;
use Storage;
use InterventionImage;

use Hash;

class TenantService
{
  //Create Tenant
  public function createTenantAdminAccount($email, $tenant_id)
  {
    User::create([
      'first_name' => 'Admin',
      'last_name' => 'Account',
      'email' => $email,
      'password' => Hash::make('password'),
      'role' => User::ROLE_ADMIN,
      'email_verified_at' => now(),
      'is_first_login' => 0
    ]);
  }

  public function updateTenantStatus($data)
  {
    $tenant = Tenant::where('id', $data['tenant_id']);
    $tenant->update(['status' => $data['tenant_status']]);
    return $tenant;
  }

  public function uploadTenantLogo($data)
  {

    if ($data['image_key'] === 'horizontal_logo') {
      $image_resize = InterventionImage::make($data['image'])->resize(507, 262)->encode('png');
    } else if ($data['image_key'] === 'vertical_logo') {
      $image_resize = InterventionImage::make($data['image'])->resize(241, 345)->encode('png');
    } else if ($data['image_key'] === 'favicon') {
      $image_resize = file_get_contents($data['image']);
    } else if ($data['image_key'] === 'side_carousel') {
      $image_resize = file_get_contents($data['image']);
    } else {
      $image_resize = InterventionImage::make($data['image'])->encode('png');
    }

    $path = "{$data['tenant_id']}/logo/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      $file_path = Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($file_path);
    } else {

      $file_path = Storage::disk('public')->put($path, $image_resize);

      $url = asset($file_path);
    }

    Image::create([
      'imageable_id' => $data['tenant_id'],
      'imageable_type' => 'App\\Models\\Central\\Tenant',
      'image_key' => $data['image_key'],
      'url' => $url
    ]);

    return $url;
  }

  public function updateTenantLogo($data)
  {
    if ($data['image_key'] === 'horizontal_logo') {
      $image_resize = InterventionImage::make($data['image'])->resize(507, 262)->encode('png');
    } else if ($data['image_key'] === 'vertical_logo') {
      $image_resize = InterventionImage::make($data['image'])->resize(241, 345)->encode('png');
    } else if ($data['image_key'] === 'favicon') {
      $image_resize = file_get_contents($data['image']);
    } else if ($data['image_key'] === 'side_carousel') {
      $image_resize = file_get_contents($data['image']);
    }  {
      $image_resize = InterventionImage::make($data['image'])->encode('png');
    }

    $image = $data['image'];

    $path = "{$data['tenant_id']}/logo/{$data['image']->hashName()}";

    if (config('app.env') == 'prod') {

      Storage::disk('s3')->put($path, $image_resize);

      $url = Storage::disk('s3')->url($path);
    } else {

      Storage::disk('public')->put($path, $image_resize);

      $file_path = Storage::url($path);

      $url = asset($file_path);
    }

    Image::updateOrCreate(
      ['id' => $data['image_id']],
      [
        'imageable_id' => $data['tenant_id'],
        'imageable_type' => 'App\\Models\\Central\\Tenant',
        'image_key' => $data['image_key'],
        'url' => $url
      ]
    );

    return $url;
  }
}
