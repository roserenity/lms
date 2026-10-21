<?php

namespace Database\Seeders;

use App\Models\TrainingCredit;
use Illuminate\Database\Seeder;

class TenantTrainingCreditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $training_credits = [
          'Leadership and Management Training', 
          'Foundational/Leadership and Management Training', 
          'Technical Training', 
          'Foundational Training'
        ];
    
        foreach ($training_credits as $key => $training_credit) {
            TrainingCredit::create([
            'name' => $training_credit,
            'uploader_id' => 1,
            ]);
        }

    }
}
