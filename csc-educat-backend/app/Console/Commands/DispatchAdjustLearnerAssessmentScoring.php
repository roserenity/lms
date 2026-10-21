<?php

namespace App\Console\Commands;

use App\Jobs\Tenants\ProcessAdjustLearnerAssessmentQuestionScoringJob;
use Illuminate\Console\Command;

class DispatchAdjustLearnerAssessmentScoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:process-adjust-learner-assessment-scoring {tenant_id : The ID of the tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process data for a specific tenant';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $this->info("Processing data for Tenant ID:" . $tenantId);
        try {
            ProcessAdjustLearnerAssessmentQuestionScoringJob::dispatch($tenantId);
        } catch (\Exception $e) {
            $this->info('Something went wrong.');
        }
    }
}