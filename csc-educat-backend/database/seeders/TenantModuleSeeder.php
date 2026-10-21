<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Course;
use App\Models\Module;
use App\Models\User;

class TenantModuleSeeder extends Seeder
{
  /**
  * Run the database seeds.
  *
  * @return void
  */
  public function run()
  {
    $user = User::where('role', USER::ROLE_INSTRUCTOR)->first();

    $course = Course::find(1);

    $modules = [
      [
        'title' => 'Module 1',
        'uploader_id' => $user->id
      ],
      [
        'title' => 'Module 2',
        'uploader_id' => $user->id
      ]
    ];

    foreach ($modules as $key => $module) {
      $course->modules()->create([
        'title' => $module['title'],
        'uploader_id' => $module['uploader_id']
      ]);
    }
  }
}
