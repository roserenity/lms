<?php

namespace App\Imports\Tenants;

use App\Jobs\Tenants\SendSuccessfulRegistrationEmail;
use App\Helpers\StandaloneConfig;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Validator;

use App\Mail\Tenants\Auth\SuccessfulRegistration;

use Illuminate\Support\Facades\DB;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ClassCategory;
use App\Models\ImportLog;
use App\Models\User;
use Mail;
use Str;
use Log;

class UsersImport implements ToCollection, WithStartRow, WithBatchInserts, WithChunkReading, ShouldQueue
{
  use Importable;

  private $errors = [];
  private $causer = null;

  public function __construct($causer)
  {
      $this->causer = $causer;
  }

  public function collection(Collection $collection)
  {
    $count = 0;
    $password = Str::random(8);

    foreach ($collection as $key => $item) {

      $mappedItem = [
        'first_name' => $item[0] ?? null,
        'middle_name' => $item[1] ?? null,
        'last_name' => $item[2] ?? null,
        'role' => $item[3] ?? null,
        'email' => $item[4] ?? null,
      ];
      
      $validator = Validator::make($mappedItem, [
        'first_name' => 'required|max:255',
        'middle_name' => 'sometimes|max:255',
        'last_name' => 'required|max:255',
        'role' => 'required|in:USER,INSTRUCTOR',
        'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL|max:255',
      ], [
        'first_name.required' => 'The first name is required.',
        'last_name.required' => 'The last name is required.',
        'role.in' => 'The selected role is invalid. Please select from (USER, INSTRUCTOR) Note: case sensitive.',
        'email.required' => 'The email address is required.',
        'email.email' => 'The email address must be a valid email format.',
        'email.unique' => 'The email address has already been taken.',
      ]);
      
      if ($validator->fails()) {
        ImportLog::create([
            'user_id' => $this->causer->id,
            'status' => 'error',
            'type' => 'Registration Bulk Import',
            'message' => $mappedItem['email'] . ' - ' . implode(', ', $validator->errors()->all())
        ]);
        continue;
      }

      $user_exists = User::where('email', $item[4])->exists();

      if(!$user_exists) {
        $user = User::create([
          'first_name' => $item[0],
          'middle_name' => $item[1],
          'last_name' => $item[2],
          'role' => strtoupper($item[3]),
          'email' => $item[4],
          'password' => $password,
        ]);
  
        $random_courses = Course::where('status', Course::IS_PUBLISH)->inRandomOrder()->take(3)->get();
  
        $apiKey = config("services.sender.key");

        if($apiKey) { 
          SendSuccessfulRegistrationEmail::dispatch([
            'name' => $user->full_name,
            'email' => $user->email,
            'password' => $password,
            'user_role' => $user->role,
            'courses' => $random_courses,
            'url' => StandaloneConfig::domainName(),
            'tenant_id' => StandaloneConfig::tenantId()
          ]);
        } else {
          Mail::to($user->email)->send(new SuccessfulRegistration([
            'name' => $user->full_name,
            'email' => $user->email,
            'password' => $password,
            'user_role' => $user->role,
            'courses' => $random_courses,
            'url' => StandaloneConfig::domainName(),
            'tenant_id' => StandaloneConfig::tenantId()
          ]));
        }
  
        if (!empty($item[5])) {
          $class_category = ClassCategory::with('courses')
            ->where('class_code', $item[5])
            ->first();
  
          if ($class_category) {
            foreach ($class_category->courses as $course) {
              $enrollment = Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->exists();
  
              Enrollment::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $course->id
              ]);
  
              if(!$enrollment) { 
                activity('user_enrollment')
                  ->causedBy($this->causer)
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
          }
        }

        $count++;

      }
    }

    ImportLog::create([
      'user_id' => $this->causer->id,
      'status' => 'success',
      'type' => 'Registration Bulk Import',
      'message' => 'Successfully imported '. $count . ' users'
    ]);

  }

  public function startRow(): int
  {
    return 2;
  }

  // public function rules(): array
  // {
  //   return [
  //     '*.0' => 'required|max:255',
  //     '*.1' => 'sometimes|max:255',
  //     '*.2' => 'required|max:255',
  //     '*.3' => 'required|in:USER,INSTRUCTOR',
  //     '*.4' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL|max:255',
  //     '*.5' => 'sometimes|exclude_if:*.3,INSTRUCTOR',
  //   ];
  // }

  // public function customValidationMessages()
  // {
  //   return [
  //     '*3.in' => 'The selected value is invalid. Please select from (USER, INSTRUCTOR) Note: case sensitive.',
  //     '*5.exists' => 'The inputted value is invalid. Data not found.',
  //   ];
  // }

  public function customValidationAttributes()
  {
    return [
      '0' => 'First name',
      '1' => 'Middle name',
      '2' => 'Last name',
      '3' => 'Role',
      '4' => 'Email address',
      '5' => 'If role is USER (class code)',
    ];
  }

  public function batchSize(): int
  {
    return 500;
  }

  public function chunkSize(): int
  {
    return 500;
  }
}
