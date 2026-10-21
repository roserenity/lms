<?php

namespace App\Console;

use Stancl\Tenancy\Database\Models\Tenant;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Illuminate\Support\Facades\Artisan;

use Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\DispatchAssessmentSetTotalPoints::class,
        Commands\DispatchLearnerSetAssessmentStatus::class,
        Commands\DispatchLearnerSetProgress::class,
        Commands\DispatchLearnerSetEvaluationProgress::class,
        Commands\DispatchLearnerAssessmentDeadlineNotification::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {

            Tenant::all()->each(function ($tenant) {
                
                Artisan::call('tenant:process-user-progress', [
                    'tenant_id' => $tenant->id,
                ]);

                Artisan::call('tenant:process-learner-assessment-deadline', [
                    'tenant_id' => $tenant->id,
                ]);
                
            });
        })
        ->timezone('Asia/Manila')
        ->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
