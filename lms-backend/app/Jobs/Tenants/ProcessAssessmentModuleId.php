<?php

namespace App\Jobs\Tenants;

use App\Models\Assessment;
use App\Models\Topic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Log;

class ProcessAssessmentModuleId implements ShouldQueue
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
            Log::info("Processing data for Tenant ID:". $this->tenantId);

            $assessments = Assessment::whereNull('module_id')->with(['assessment_has_topics', 'assessment_has_sub_topics'])->get();

            foreach ($assessments as $assessment) {
                $module_id = null;
            
                if ($assessment->assessment_has_topics->isNotEmpty()) {
                    $module_id = $assessment->assessment_has_topics->first()->module_id ?? null;
                }
            
                if (!$module_id && $assessment->assessment_has_sub_topics->isNotEmpty()) {
                    $module_id = $assessment->assessment_has_sub_topics->first()->module_id ?? null;
                }
            
                if ($module_id) {
                    $assessment->module_id = $module_id;
                    $assessment->save();
                }
            }
        } catch (\Exception $e) {
            Log::error('ProcessAssessmentModuleId failed: ' . $e->getMessage());
        }
    
        Log::info("Finished processing data");
    }
}
