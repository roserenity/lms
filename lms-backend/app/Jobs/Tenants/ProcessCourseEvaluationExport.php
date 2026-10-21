<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Teacher\GradingCourseEvaluationList;
use App\Models\CourseEvaluation;
use App\Models\Exports;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Log;

class ProcessCourseEvaluationExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $exportRecordId;
    private $courseEvaluationId;
    private $submittedFrom;
    private $submittedTo;
    private $search;

    public function __construct($exportRecordId, $courseEvaluationId, $submittedFrom, $submittedTo, $search)
    {
        $this->exportRecordId = $exportRecordId;
        $this->courseEvaluationId = $courseEvaluationId;
        $this->submittedFrom = $submittedFrom;
        $this->submittedTo = $submittedTo;
        $this->search = $search;
    }

    public function handle()
    {
        $exportRecord = Exports::find($this->exportRecordId);

        try {
            $courseEvaluation = CourseEvaluation::with('course')->findOrFail($this->courseEvaluationId);

            $export = new GradingCourseEvaluationList(
                $this->courseEvaluationId,
                $this->submittedFrom,
                $this->submittedTo,
                $this->search
            );

            $filename = 'exports/course_evaluations/' . Str::limit(Str::slug($courseEvaluation->title, '_'), 50, '') . '_' . now()->format('Ymd_His') . '.csv';

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
