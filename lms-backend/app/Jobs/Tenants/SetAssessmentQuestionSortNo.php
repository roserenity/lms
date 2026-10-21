<?php

namespace App\Jobs\Tenants;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Log;

class SetAssessmentQuestionSortNo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenantId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tenantId)
    {
        //
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

        $assessments = Assessment::withTrashed()->select(['id'])->get();

        foreach($assessments as $assessment) {
            $assessment_questions = AssessmentQuestion::withTrashed()->where('assessment_id', $assessment->id)->get();

            $sort_no = 0;
            foreach($assessment_questions as $assessment_question) {
                $assessment_question->sort_no = $sort_no;
                $assessment_question->save();
                $sort_no++;
            }

        }

        Log::info("Finished processing data");
    }
}
