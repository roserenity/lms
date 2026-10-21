<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Enrollment;
use App\Models\User;

class TenantEnrollmentSeeder extends Seeder
{
  /**
  * Run the database seeds.
  *
  * @return void
  */
  public function run()
  {
    $user = User::where('role', USER::ROLE_USER)->first();

    Enrollment::firstOrCreate([
      'user_id' => $user->id,
      'course_id' => 1
    ]);
  }
}
