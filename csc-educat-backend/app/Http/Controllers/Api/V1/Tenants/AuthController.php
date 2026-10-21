<?php

namespace App\Http\Controllers\Api\V1\Tenants;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Laravel\Passport\TokenRepository;
// use Laravel\Passport\RefreshTokenRepository;
use App\Mail\Tenants\Auth\SuccessfulRegistration;
use App\Mail\Tenants\Auth\VerifyEmail;
use App\Mail\Tenants\Course\CourseEnrollment;

use App\Http\Requests\Tenants\Auth\EmailVerificationRequest;
use App\Http\Requests\Tenants\Auth\ForgotPasswordFormRequest;
use App\Http\Requests\Tenants\Auth\LoginRequest;
use App\Http\Requests\Tenants\Auth\ResetPasswordFormRequest;
use App\Http\Requests\Tenants\Auth\UpdatePasswordRequest;
use App\Http\Requests\Tenants\Auth\RegisterRequest;
use App\Jobs\Tenants\SendSuccessfulEnrollmentEmail;
use App\Jobs\Tenants\SendSuccessfulRegistrationEmail;
use App\Jobs\Tenants\SendVerificationEmail;
use App\Models\ClassCategory;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Document;
use App\Services\Tenants\Auth\PasswordService;
use App\Services\Tenants\ImageService;
use App\Notifications\VerifyEmailNotification;

use Carbon\Carbon;
use DB;

use Spatie\Activitylog\Models\Activity;

use App\Helpers\StandaloneConfig;
use App\Models\User;
use Hash;
use Auth;
use Exception;

class AuthController extends Controller
{
  protected $tokenRepository;
  protected $refreshTokenRepository;

  //User authentication
  public function __construct()
  {
    $this->middleware('auth:api')->except(['login', 'register', 'verifyEmail', 'sendResetPasswordForm', 'showResetPasswordForm', 'resetPassword', 'sendVerificationEmail']);
    $this->middleware('verified')->only('user');
    //$this->middleware('signed')->only(['verifyEmail', 'throttle:6,1']);
    $this->middleware('signed')->only(['throttle:100,5']);
    $this->tokenRepository = app(TokenRepository::class);
    // $this->refreshTokenRepository = app(RefreshTokenRepository::class);
  }

  public function user()
  {
    $user = auth()->guard('api')->user();
    $user->terms_and_conditions = null;

    if (!$user->has_accepted_terms) {
      $document = Document::where('show', 1)->first();
      if($document) {
        $user->terms_and_conditions = $document;
      }
    }

    if($user->role == User::ROLE_ADMIN) {
      $user->ticket_count = $user->getTicketCount();
    }

    return response()->json($user);
  }

  public function sendVerificationEmail()
  {
    $user = auth()->guard('api')->user();

    // email verification v1
    // $user->notify(new VerifyEmailNotification([
    //   'name' => $user->full_name,
    //   'url' => StandaloneConfig::domainName(),
    //   'tenant_id' => StandaloneConfig::tenantId()
    // ]));

    // email verification v2
    if(!$user) {
      return $this->sendResponse([], 'Something went wrong', 500);
    }

    $creds = implode(':', [$user->uuid, $user->email]);
    $hash = Crypt::encryptString($creds);

    $apiKey = config("services.sender.key");

    if($apiKey) {
      try {
        SendVerificationEmail::dispatch([
          'name' => $user->full_name,
          'url' => StandaloneConfig::domainName() . '/verify-email?' . http_build_query([
            'hash' => $hash
          ]),
          'email' => $user->email
        ]);
      } catch (Exception $e) {
        Log::error('Email Verification Error' . $e );
        return $this->sendResponse([], 'Something went wrong', 500);
      }
    } else {
      Mail::to($user->email)->send(new VerifyEmail([
        'name' => $user->full_name,
        'url' => StandaloneConfig::domainName() . '/verify-email?' . http_build_query([
          'hash' => $hash
        ])
      ]));
    }

    return $this->sendResponse($user, 'Verification link sent.', 200);
  }

  // email verification v1
  // public function verifyEmail(EmailVerificationRequest $request)
  // {
  //   $request->fulfill();

  //   $user = auth()->guard('api')->user();

  //   return $this->sendResponse($user, 'Email has been verified.', 201);
  // }

  // email verification 2
  public function verifyEmail(Request $request)
  {
    $unhashed_value = Crypt::decryptString($request->hash);
    $creds = explode(':', $unhashed_value);

    $user = User::where('uuid', $creds[0])->where('email', $creds[1])->first();

    if($user) {
      $user->email_verified_at = now();

      $user->save();
    }

    return $this->sendResponse($user, 'Email has been verified.', 201);
  }

  public function sendResetPasswordForm(ForgotPasswordFormRequest $request)
  {
    $user = User::where('email', $request->email)->first();

    if ($user->status === 'inactive') {
      return response()->json([
        'message' => 'Something went wrong.',
        'errors' => [
          'status' => ['Your account has been disabled. Contact support for assistance.']
        ]
      ], 403);
    }

    $status = PasswordService::sendResetLink([
      'email' => $request->email,
      'name' => $user->full_name,
      'url' => StandaloneConfig::domainName(),
      'tenant_id' => StandaloneConfig::tenantId()
    ]);

    if (!$status) {
      return $this->sendResponse([], 'Something went wrong, please try again later', 500);
      //return $this->sendResponse([], 'We already sent you a password reset link.', 200);
    }

    return $this->sendResponse($request->safe()->only('email'), 'Password reset link sent.', 201);
  }

  public function showResetPasswordForm($token)
  {
    return response()->json([
      'token' => $token,
      'email' => request('email'),
    ]);
  }

  public function resetPassword(ResetPasswordFormRequest $request)
  {
    $status = Password::reset($request->validated(), function ($user, $password) {
      $user->forceFill([
        'password' => Hash::make($password),
      ]);

      $user->save();

      event(new PasswordReset($user));

      DB::table('password_resets')->where('email', $user->email)->delete();

    });

    if ($status === Password::INVALID_TOKEN) {
      return $this->sendResponse([], 'Invalid token.', 400);
    }

    if ($status === Password::PASSWORD_RESET) {
      return $this->sendResponse([], 'Successfully reset your password. Proceed to login page.', 201);
    }

    return $status;
  }

  public function updatePassword(UpdatePasswordRequest $request)
  {
    $user = auth()->guard('api')->user();

    $user->password = $request->password;

    if ($user->is_first_login) {
      $user->is_first_login = false;
    }

    $user->save();

    $user_tokens = $user->tokens;

    foreach($user_tokens as $token) {
        $token->revoke();   
    }

    return $this->sendResponse($user, 'User details.', 200);
  }

  public function updateAcceptedTerms()
  {
    $user = auth()->guard('api')->user();

    if (!$user->has_accepted_terms) {
      $user->has_accepted_terms = 1;
    }

    $user->save();
    
    return $this->sendResponse($user, 'User details.', 200);
  }

  public function updateNeedsInfoFlag()
  {
    $user = auth()->guard('api')->user();

    if (!$user->has_accepted_terms) {
      $user->needs_info_update = 0;
    }

    $user->save();
    
    return $this->sendResponse($user, 'User details.', 200);
  }

  public function login(LoginRequest $request)
  {
    $user = User::firstWhere('email', $request->email);

    if ($user->status === 'inactive') {
      return response()->json([
        'message' => 'Something went wrong.',
        'errors' => [
          'status' => ['Your account has been disabled. Contact support for assistance.']
        ]
      ], 403);
    }

    $currentToken = $user->tokens()->first();

    if ($user->role === User::ROLE_USER) {
      // delete tokens of user before creating new one
      $tokens = $user->tokens;

      foreach ($tokens as $token) {
          // Revoke access token
          $token->revoke();
      }
    }

    if (Auth::attempt($request->validated())) {
      $user['access_token'] = $user->createToken(StandaloneConfig::tenantId(), [])->accessToken;
    }

    return $this->sendResponse($user, 'Successfully generate an access token.', 201);
  }

  public function register(RegisterRequest $request)
  {
    $user = User::create($request->safe()->except('class_category_id'));

    if (!empty($request->class_category_id)) {
      $class_category = ClassCategory::with([
        'courses' => fn ($query) => $query->where('status', Course::IS_PUBLISH)
      ])
        ->where('id', $request->class_category_id)
        ->first();

      foreach ($class_category->courses as $course) {
        $enrollment = Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->exists();

        Enrollment::firstOrCreate([
          'user_id' => $user->id,
          'course_id' => $course->id
        ]);

        if(!$enrollment) {
          activity('user_enrollment')
            ->causedBy($user)
            ->withProperties([
                'user_id' => $user->id,
                'email' => $user->email,
                'course_id' => $course->id,
            ])
            ->event('created')
            ->performedOn($user)
            ->log('User Enrolled');
        }
        
      }

      $creds = implode(':', [$user->uuid, $user->email]);
      $hash = Crypt::encryptString($creds);

      $apiKey = config("services.sender.key");

      if($apiKey) { 
        SendSuccessfulEnrollmentEmail::dispatch([
          'name' => $user->full_name,
          'courses' => $class_category->courses,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]);
      } else {
        Mail::to($user->email)->send(new CourseEnrollment([
          'name' => $user->full_name,
          'courses' => $class_category->courses,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]));
      }

    } else {
      $courses = Course::where('status', Course::IS_PUBLISH)->inRandomOrder()->take(3)->get();

      $creds = implode(':', [$user->uuid, $user->email]);
      $hash = Crypt::encryptString($creds);

      $apiKey = config("services.sender.key");

      if($apiKey) { 
        SendSuccessfulRegistrationEmail::dispatch([
          'name' => $user->full_name,
          'email' => $user->email,
          'password' => NULL,
          'user_role' => $user->role,
          'courses' => $courses,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]);
      } else {
        Mail::to($user->email)->send(new SuccessfulRegistration([
          'name' => $user->full_name,
          'email' => $user->email,
          'password' => NULL,
          'user_role' => $user->role,
          'courses' => $courses,
          'url' => StandaloneConfig::domainName(),
          'tenant_id' => StandaloneConfig::tenantId()
        ]));
      }

    }

    // event(new Registered($user));

    // email verification v1
    // $user->notify(new VerifyEmailNotification([
    //   'name' => $user->full_name,
    //   'url' => StandaloneConfig::domainName(),
    //   'tenant_id' => StandaloneConfig::tenantId()
    // ]));

    // email verification v2
    $creds = implode(':', [$user->uuid, $user->email]);
    $hash = Crypt::encryptString($creds);

    $apiKey = config("services.sender.key");

    if($apiKey) {
      try {
        SendVerificationEmail::dispatch([
          'name' => $user->full_name,
          'url' => StandaloneConfig::domainName() . '/verify-email?' . http_build_query([
            'hash' => $hash
          ]),
          'email' => $user->email
        ]);
      } catch (Exception $e) {
        Log::error('Email Verification Error' . $e );
        return $this->sendResponse([], 'Something went wrong', 500);
      }
    } else {
      Mail::to($user->email)->send(new VerifyEmail([
        'name' => $user->full_name,
        'url' => StandaloneConfig::domainName() . '/verify-email?' . http_build_query([
          'hash' => $hash
        ])
      ]));
    }

    $user['access_token'] = $user->createToken(StandaloneConfig::tenantId(), [])->accessToken;

    return $this->sendResponse($user, 'Successfully generate an access token.', 201);
  }

  public function logout()
  {
    $access_token = auth()->guard('api')->user()->token();
    $access_token->revoke();

    return $this->sendResponse([], 'Successfully logout!', 200);
  }

  public function updateDetails(Request $request)
  {
    $request->validate([
      'first_name' => 'required|max:255',
      'middle_name' => 'sometimes|max:255',
      'last_name' => 'required|max:255',
      'suffix' => 'sometimes|max:255',
      'gender' => 'nullable|sometimes|in:M,m,F,f',
      'data' => 'sometimes',
      'email' => 'sometimes|email|unique:users,email,' . $this->authUser()->id . ',id'
    ]);

    $this->authUser()->update([
      'first_name' => $request->first_name,
      'middle_name' => $request->middle_name,
      'last_name' => $request->last_name,
      'suffix' => $request->suffix,
      'gender' => $request->gender,
      'data' => $request->data,
      'email' => $request->email
    ]);

    $this->authUser()->refresh();

    return $this->sendResponse([], 'Account details updated.', 200);
  }

  public function updateProfilePhoto()
  {
    request()->validate([
      'image' => 'required|mimes:jpg,jpeg,png'
    ]);

    ImageService::uploadUserImage([
      'model_id' => $this->authUser()->id,
      'model_type' => 'App\\Models\\User',
      'image' => request()->file('image')
    ]);

    return $this->sendResponse([], 'Profile photo updated.', 200);
  }

  public function loginHistory(Request $request)
  {
    return $this->authUser()->tokens()
      ->when($request->date_from && $request->date_to, function ($query) use ($request) {
        return $query->whereDate('created_at', '>=', $request->date_from)->whereDate('created_at', '<=', $request->date_to);
      })
      ->get(['created_at', 'revoked']);
  }

  public function activityLogs(Request $request)
  {

    $logs = Activity::where('subject_id', $this->authUser()->id)
        ->when($request->year && $request->month, function ($query) use ($request) {
          return $query->whereYear('created_at', $request->year)
          ->whereMonth('created_at', $request->month);
        })
        ->get()
        ->filter(function ($log) {
            $courseId = data_get($log, 'properties.course_id');
            $causerId = data_get($log, 'causer_id');
            
            return Course::where('id', $courseId)->exists() && User::where('id', $causerId)->exists();
        })
        ->map(function ($log) {
            $courseId = data_get($log, 'properties.course_id');
            $causerId = data_get($log, 'causer_id');

            $log->course = $courseId ? Course::find($courseId)->toArray() : null;
            $log->causer = $causerId ? User::find($causerId)->toArray() : null;
            
            return $log;
        })
        ->toArray();

      $tokens = DB::table('oauth_access_tokens')
        ->where('user_id', $this->authUser()->id)
        ->when($request->year && $request->month, function ($query) use ($request) {
            return $query->whereYear('created_at', $request->year)
                ->whereMonth('created_at', $request->month);
        })
        ->get()
        ->map(function ($token) {
            $token->created_at = Carbon::parse($token->created_at)
                ->setTimezone('UTC')
                ->format('Y-m-d\TH:i:s.u\Z');
            return $token;
        })->toArray();

    $mergedArray = collect(array_merge($logs, $tokens))
      ->sortByDesc('created_at')
      ->values()
      ->toArray();

    // $mergedArray = array_merge($logs, $tokens);

    // $mergedCollection = collect($mergedArray)->sortBy(function ($item) {
    //   // Convert different 'created_at' formats to Unix timestamp
    //   return strtotime(str_replace('T', ' ', str_replace(['.000000Z', 'Z'], '', $item['created_at'])));
    // });

    // $finalArray = $mergedCollection->toArray();
    
    return $mergedArray;
  }
}
