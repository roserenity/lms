<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use Illuminate\Database\Seeder;

class TenantCertificateTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        $certificate_template = CertificateTemplate::create([
            'name' => 'Default Certificate of Completion Template',
            'certificate_category_id' => 1,
            'is_default' => 1,
            'uploader_id' => 1,
            'content' => json_encode([
                ["type" => "Paragraph", "text" => "This"],
                ["type" => "Header", "text" => "CERTIFICATE OF COMPLETION"],
                ["type" => "Paragraph", "text" => "is awarded to"],
                ["type" => "Subheader", "text" => "{{ name }}"],
                ["type" => "Paragraph", "text" => "for having successfully completed the training on"],
                ["type" => "Subheader", "text" => "{{ course_title }}"],
                ["type" => "Paragraph", "text" => "(credit of {{ credit_training_hours }} hour {{ training_credits }})"],
                ["type" => "Paragraph", "text" => "conducted by Aster Academy."],
                ["type" => "Paragraph", "text" => "Given this {{ date_received }}"]
            ], JSON_UNESCAPED_UNICODE)
        ]);

        $certificate_template_attestor = $certificate_template->attestor()->create([
            'name' => 'ALEX SAMPLE',
            'designation' => 'Program Director',
            'info' => 'Aster Academy'
        ]);

        $certificate_template_attestor->image()->create([
            'url' => asset('img/certificate/default-attestor.png'),
        ]);

        $certificate_template_signatory = $certificate_template->signatory()->create([
            'name' => 'JORDAN EXAMPLE',
            'designation' => 'Executive Director'
        ]);

        $certificate_template_signatory->image()->create([
            'url' => asset('img/certificate/default-signatory.png'),
        ]);

    }
}
