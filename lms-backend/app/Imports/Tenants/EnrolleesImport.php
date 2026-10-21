<?php

namespace App\Imports\Tenants;

use App\Jobs\Tenants\SendSuccessfulEnrollmentEmail;
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

use Illuminate\Support\Facades\Auth;

use App\Mail\Tenants\Course\CourseEnrollment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Enrollment;
use App\Models\User;
use App\Models\Course;
use App\Models\Invoice;
use App\Models\ImportLog;
use Mail;
use Log;

class EnrolleesImport implements ToCollection, WithStartRow, WithBatchInserts, WithChunkReading, ShouldQueue
{
  use Importable;

  protected $course_id;
  protected $causer_id;

  public function __construct($course_id, $causer_id)
  {
      $this->course_id = $course_id;
      $this->causer_id = $causer_id;
  }

  public function refId()
  {
      $format = 'ABCDEF';
      $randStr = str_replace(
          ['A', 'B', 'C', 'D', 'E', 'F'],
          [
              Str::upper(Str::random(1)),
              Str::upper(Str::random(1)),
              Str::upper(Str::random(1)),
              Str::upper(Str::random(1)),
              Str::upper(Str::random(1)),
              Str::upper(Str::random(1)),
          ],
          $format
      );

      return date('ymhis') . $randStr;
  }

  public function collection(Collection $collection)
  {
    try {
      $course = Course::find($this->course_id);

      $count = 0;
      foreach ($collection as $key => $item) {
          
        $validator = Validator::make($item->toArray(), [
          '0' => 'required|email|exists:users,email',
          '1' => 'required|in:INTERNAL EMPLOYEE,REGULAR EMPLOYEE,PARTNER GRANTEE'
        ], [
          '0.required' => 'The email address is required.',
          '0.email' => 'The email address must 
          
          a valid email format.',
          '0.exists' => 'The email address must be registered in the lms',
        ]);

        if($validator->fails()) {
          ImportLog::create([
              'user_id' => $this->causer_id,
              'status' => 'error',
              'course_id' => $course->id,
              'type' => 'Enrollment Bulk Import',
              'message' => $item['0'] . ' - ' . implode(', ', $validator->errors()->all())
          ]);
          continue;

        }
        $user = User::where('email', $item[0])->first();
        $course = Course::where('id', $this->course_id)->first();
        $enrollment = Enrollment::where('user_id', $user->id)->where('course_id', $this->course_id)->exists();

        if(!$enrollment) {
          $invoice = Invoice::where('course_id', $this->course_id)->where('user_id', $user->id)->first();

          if(!$invoice) {
            $ref_id = self::refId();

            Invoice::create([
              'reference_id' => $ref_id,
              'user_id' => $user->id,
              'course_id' => $course->id,
              'mode_of_payment' => Invoice::OFFLINE,
              'status' => $course->price > 0 ? Invoice::PAYMENT_PENDING : Invoice::PAYMENT_FREE,
              'amount' => $course->price,
              'is_free' => $course->is_free,
              'type_of_participant' => Invoice::PARTICIPANT_TYPE_LABELS[$item[1]],
              'enroll_by_id' => $this->causer_id
            ]);
          }

          if($course->price === 0) {

            $enrollment = Enrollment::where('user_id', $user->id)->where('course_id', $this->course_id)->first();

            if(!$enrollment) {
              $causer = User::find($this->causer_id);
              activity('user_enrollment')
                ->causedBy($causer)
                ->withProperties([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'course_id' => $this->course_id,
                ])
                ->event('created')
                ->performedOn($user)
                ->log('User Enrolled');

                
              $apiKey = config("services.sender.key");

              if($apiKey) {
                SendSuccessfulEnrollmentEmail::dispatch([
                  'name' => $user->full_name,
                  'courses' => [$course],
                  'url' => StandaloneConfig::domainName(),
                  'tenant_id' => StandaloneConfig::tenantId(),
                  'email' => $user->email
                ]);
              } else {
                Mail::to($user->email)->send(new CourseEnrollment([
                    'name' => $user->full_name,
                    'courses' => [$course],
                    'url' => StandaloneConfig::domainName(),
                    'tenant_id' => StandaloneConfig::tenantId()
                ]));
              }
            }
            
            Enrollment::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $this->course_id
            ]);

          }
          
          ImportLog::create([
              'user_id' => $this->causer_id,
              'status' => 'success',
              'course_id' => $course->id,
              'type' => 'Enrollment Bulk Import',
              'message' => $item['0'] . ' enrolled '
          ]);

          $count++;
        }
      }
      
      if($count > 0) {
        ImportLog::create([
          'user_id' => $this->causer_id,
          'status' => 'success',
          'course_id' => $course->id,
          'type' => 'Enrollment Bulk Import',
          'message' => 'Successfully enrolled ' . $count . ' ' . ($count === 1 ? 'user' : 'users')
        ]);
      }
    } catch (\Exception $e) {
      Log::info('Import error: '. $e);
      throw $e;
    }
    
  }

  public function startRow(): int
  {
    return 2;
  }

  public function rules(): array
  {
    return [
      '*.0' => 'required|email|exists:users,email',
    ];
  }

  public function customValidationAttributes()
  {
    return [
      '0' => 'Email address',
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
