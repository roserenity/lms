<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Teacher\GradingAssessmentSummary;
use App\Models\Assessment;
use App\Models\Exports;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

use Log;

class ProcessAssessmentSummaryExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $export_record_id;
    private $assessment_id;
    private $submitted_from;
    private $submitted_to;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $assessment_id, $submitted_from, $submitted_to)
    {
        //
        $this->export_record_id = $export_record_id;
        $this->assessment_id = $assessment_id;
        $this->submitted_from = $submitted_from;
        $this->submitted_to = $submitted_to;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $exportRecord = Exports::find($this->export_record_id);

        try {
            $assessment = Assessment::find($this->assessment_id);

            $export = new GradingAssessmentSummary(
                $this->assessment_id,
                $this->submitted_from,
                $this->submitted_to,
            );

            $filename = 'exports/assessment/' . Str::limit(Str::slug($assessment->title, '_'), 50, '') . '_' . now()->format('Ymd_His') . '.csv';

            if (config('app.env') == 'prod') {
                Excel::store($export, $filename, 's3', \Maatwebsite\Excel\Excel::CSV);
                $url = Storage::disk('s3')->url($filename);
            } else {
                Excel::store($export, $filename, 'public', \Maatwebsite\Excel\Excel::CSV);
                $url = Storage::disk('public')->url($filename);
            }

            $exportRecord->update([
                'filename' => basename($filename),
                'path' => $filename,
                'url' => $url,
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            $exportRecord->update(['status' => 'failed']);
        }
    }
}
