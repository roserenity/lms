<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Teacher\GradingModuleEvaluationSummary;
use App\Models\Exports;
use App\Models\ModuleEvaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Log;

class ProcessModuleEvaluationSummaryExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $exportRecordId;
    private $moduleEvaluationId;
    private $submittedFrom;
    private $submittedTo;
    private $courseId;

    public function __construct($exportRecordId, $moduleEvaluationId, $submittedFrom, $submittedTo, $courseId)
    {
        $this->exportRecordId = $exportRecordId;
        $this->moduleEvaluationId = $moduleEvaluationId;
        $this->submittedFrom = $submittedFrom;
        $this->submittedTo = $submittedTo;
        $this->courseId = $courseId;
    }

    public function handle()
    {
        $exportRecord = Exports::find($this->exportRecordId);

        try {
            $moduleEvaluation = ModuleEvaluation::select(['module_id', 'id'])->find($this->moduleEvaluationId);

            $export = new GradingModuleEvaluationSummary(
                $this->exportRecordId,
                $this->moduleEvaluationId,
                $this->submittedFrom,
                $this->submittedTo,
                $this->courseId
            );

            $filename = 'exports/module_evaluations/' . Str::limit(Str::slug($moduleEvaluation->title, '_'), 50, '') . '_' . now()->format('Ymd_His') . '.csv';

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