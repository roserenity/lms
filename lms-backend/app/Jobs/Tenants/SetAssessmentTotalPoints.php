<?php

namespace App\Jobs\Tenants;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Assessment;

use Log;
use Stancl\Tenancy\Concerns\HasATenantArgument;

class SetAssessmentTotalPoints implements ShouldQueue
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
        Log::info("Processing data for Tenant ID:". $this->tenantId);

        $assessments = Assessment::with('assessment_questions')->get();

        foreach($assessments as $assessment) {
            $sum = $assessment->assessment_questions->sum('score');
            $assessment->update(['total_points' => $sum]);
            $assessment->save();
        }
        Log::info("Finished processing data");
    }
}
