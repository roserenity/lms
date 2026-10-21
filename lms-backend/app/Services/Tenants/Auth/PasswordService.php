<?php

namespace App\Services\Tenants\Auth;

use App\Jobs\Tenants\SendResetPasswordEmail;
use App\Mail\Tenants\Auth\ResetPasswordLink;
use App\Services\Tenants\ResendService;
use Illuminate\Support\Facades\Log;

use DB;
use Exception;
use Hash;
use Mail;
use Str;

class PasswordService
{

  public static function sendResetLink($data)
  {
    $exist = DB::table('password_resets')->where('email', $data['email'])->first();

    if($exist) {
      DB::table('password_resets')->where('email', $data['email'])->delete();
    }

    // if (!$exist) {
    $token = Str::random(64);

    $success = DB::table('password_resets')->insert([
      'email' => $data['email'],
      'token' => Hash::make($token),
      'created_at' => now(),
    ]);

    if ($success) {
      $url = $data['url'] . '/reset-password/' . $token . '?email=' . urlencode($data['email']);

      try {
        $apiKey = config("services.sender.key");

        if($apiKey) { 
          try {
            
            SendResetPasswordEmail::dispatch([
              'name' => $data['name'],
              'url' => $url,
              'tenant_id' => $data['tenant_id'],
              'email' => $data['email']
            ]);
          } catch(Exception $e)
          {
            Log::info('Mail error : '.$e);
            return false;
          }
        } else {
          Mail::to($data['email'])->send(new ResetPasswordLink([
            'name' => $data['name'],
            'url' => $url,
            'tenant_id' => $data['tenant_id']
          ]));
        }
        
      } catch(\Exception $e) {
        Log::info('Mail error : '.$e);
        return false;
      }
      
    } else {
      return false;
    }

    return true;
    //}
    //return false;
  }
}
