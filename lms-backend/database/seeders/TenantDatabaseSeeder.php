<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
          TenantClassCategorySeeder::class,
          TenantUserSeeder::class,
          TenantCourseSeeder::class,
          TenantModuleSeeder::class,
          TenantTopicSeeder::class,
          TenantAssessmentSeeder::class,
          TenantEnrollmentSeeder::class,
          TenantCertificateTemplateCategorySeeder::class,
          TenantCertificateTemplateSeeder::class,
          TenantTrainingCreditSeeder::class
        ]);
    }
}
