<?php

namespace App\Console\Commands;


use App\Jobs\Tenants\ProcessAssessmentModuleId;
use Illuminate\Console\Command;
use Log;

class DispatchFixAssessmentModuleId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:process-fix-assessment-module-id {tenant_id : The ID of the tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            ProcessAssessmentModuleId::dispatch($tenantId);
            $this->info("Successfully dispatched ProcessAssessmentModuleId job for Tenant ID: {$tenantId}");
            Log::info("ProcessAssessmentModuleId job dispatched for Tenant ID: {$tenantId}");
        } catch (\Exception $e) {
            $this->error("ProcessAssessmentModuleId job Failed to process data for Tenant ID: {$tenantId}");
            Log::error("ProcessAssessmentModuleId job Error processing Tenant ID: {$tenantId}", [
                'exception' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
        }
        return 0;
    }
}