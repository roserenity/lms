<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Tenants\SaveUserEvaluationProgress;
use Log;

class DispatchLearnerSetEvaluationProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:process-user-evaluation-progress {tenant_id : The ID of the tenant}';

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
        $this->info("Processing data for Tenant ID: {$tenantId}");
        try {
            SaveUserEvaluationProgress::dispatch($tenantId);
            $this->info("Successfully dispatched SaveUserEvaluationProgress job for Tenant ID: {$tenantId}");
            Log::info("SaveUserEvaluationProgress job dispatched for Tenant ID: {$tenantId}");
        } catch (\Exception $e) {
            $this->error("SaveUserEvaluationProgress job Failed to process data for Tenant ID: {$tenantId}");
            Log::error("SaveUserEvaluationProgress job Error processing Tenant ID: {$tenantId}", [
                'exception' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
        }
        return 0;
    }
}
