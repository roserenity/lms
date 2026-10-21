<?php

namespace App\Jobs\Tenants;

use App\Models\Course;
use App\Models\TrainingCredit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Log;

class SetTrainingCredit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $courses = Course::withTrashed()->select(['id', 'training_credits', 'training_credit'])->get();

            foreach($courses as $course) {

                $training_credit = TrainingCredit::where('name', $course->training_credits)->first();

                if($training_credit) 
                {
                    $course->training_credit = $training_credit->id;

                    $course->update();

                    Log::info("Updated course ID {$course->id} with training_credit ID {$training_credit->id}");

                }

            }

        }  catch (\Exception $e) {
            
            Log::info($e);

        }
    }
}
