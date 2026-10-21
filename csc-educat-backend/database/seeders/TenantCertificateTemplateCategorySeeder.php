<?php

namespace Database\Seeders;

use App\Models\CertificateTemplateCategory;
use Illuminate\Database\Seeder;

class TenantCertificateTemplateCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        CertificateTemplateCategory::create([
            'name' => 'CERTIFICATE OF COMPLETION',
            'uploader_id' => 1
        ]);

    }
}
