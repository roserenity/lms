<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Module;
use App\Models\User;

class TenantTopicSeeder extends Seeder
{
  /**
  * Run the database seeds.
  *
  * @return void
  */
  public function run()
  {
    $user = User::where('role', USER::ROLE_INSTRUCTOR)->first();

    $modules = Module::all();

    foreach ($modules as $key => $module) {
      $topic = $module->topics()->create([
        'course_id' => $module->course_id,
        'title' => 'Sample topic',
        'uploader_id' => $user->id
      ]);

      // Learners see a module's items through module_content, as TopicController creates them.
      $module->module_content()->create([
        'module_id' => $module->id,
        'contentable_id' => $topic->id,
        'contentable_type' => 'App\\Models\\Topic',
        'sort_no' => 1
      ]);
    }
  }
}
