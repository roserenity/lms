<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\ClassCategory;
use App\Models\Course;
use App\Models\User;

use DB;

class TenantCourseSeeder extends Seeder
{
  /**
  * Run the database seeds.
  *
  * @return void
  */
  public function run()
  {
    $user = User::where('role', USER::ROLE_INSTRUCTOR)->first();

    $course = Course::create([
      'title' => 'Sample Course',
      'status' => 'PUBLISH',
      'price' => 0,
      'is_free' => 1,
      'training_hours' => 1,
      'credit_training_hours' => 1,
      'training_credits' => 1,
      'uploader_id' => $user->id
    ]);

    $class_categories = ClassCategory::all();

    $course->class_categories()->sync($class_categories);
  }
}
