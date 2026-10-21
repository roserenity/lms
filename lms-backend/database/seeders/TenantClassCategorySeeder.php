<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\ClassCategory;

class TenantClassCategorySeeder extends Seeder
{
  /**
  * Run the database seeds.
  *
  * @return void
  */
  public function run()
  {
    $classes = [
      [
        'class_code' => '123ABC',
        'class_name' => 'Class 1'
      ],
      [
        'class_code' => '456DEF',
        'class_name' => 'Class 2'
      ]
    ];

    foreach ($classes as $key => $item) {
      ClassCategory::create([
        'class_code' => $item['class_code'],
        'class_name' => $item['class_name'],
      ]);
    }
  }
}
