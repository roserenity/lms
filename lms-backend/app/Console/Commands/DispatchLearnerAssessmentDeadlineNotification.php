<?php

namespace App\Console\Commands;

use App\Jobs\Tenants\SendLearnerAssessmentDeadlineNotification;
use Illuminate\Console\Command;
use Log;

class DispatchLearnerAssessmentDeadlineNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:process-learner-assessment-deadline {tenant_id : The ID of the tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process assessment deadline notification for a specific tenant';

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
        $this->info("Processing data for Tenant ID: {$tenantId}");
        try {
            SendLearnerAssessmentDeadlineNotification::dispatch($tenantId);
            $this->info("Successfully dispatched SendLearnerAssessmentDeadlineNotification job for Tenant ID: {$tenantId}");
            Log::info("SendLearnerAssessmentDeadlineNotification job dispatched for Tenant ID: {$tenantId}");
        } catch (\Exception $e) {
            $this->error("SendLearnerAssessmentDeadlineNotification job Failed to process data for Tenant ID: {$tenantId}");
            Log::error("SendLearnerAssessmentDeadlineNotification job Error processing Tenant ID: {$tenantId}", [
                'exception' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
        }
        return 0;
    }
}
