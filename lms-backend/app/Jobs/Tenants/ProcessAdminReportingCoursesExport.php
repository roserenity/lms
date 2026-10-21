<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Teacher\ReportingCoursesSummary;
use App\Models\Course;
use App\Models\Exports;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use Log;

class ProcessAdminReportingCoursesExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $export_record_id;
    private $search;
    private $user_id;
    private $date_from;
    private $date_to;
    private $status;
    private $type_of_learning;
    private $enrolled_from;
    private $enrolled_to;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $search, $user_id, $date_from, $date_to, $status, $type_of_learning, $enrolled_from, $enrolled_to)
    {
        //

        $this->export_record_id = $export_record_id;
        $this->search = $search;
        $this->user_id = $user_id;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->status = $status;
        $this->type_of_learning = $type_of_learning;
        $this->enrolled_from = $enrolled_from;
        $this->enrolled_to = $enrolled_to;  

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        
        ini_set('memory_limit', -1); 
        
        $exportrecord = Exports::find($this->export_record_id);

        try {

            $export = new ReportingCoursesSummary(
                $this->search,
                $this->user_id,
                $this->date_from,
                $this->date_to,
                $this->status,
                $this->type_of_learning,
                $this->enrolled_from,
                $this->enrolled_to
            );
            
            $filename = 'exports/course/reporting_' . now()->format('Ymd_His') . '.csv';

            if (config('app.env') == 'prod') {
                Excel::store($export, $filename, 's3', \Maatwebsite\Excel\Excel::CSV);
                $url = Storage::disk('s3')->url($filename);
            } else {
                Excel::store($export, $filename, 'public', \Maatwebsite\Excel\Excel::CSV);
                $url = Storage::disk('public')->url($filename);
            }

            $exportrecord->update([
                'filename' => basename($filename),
                'path' => $filename,
                'url' => $url,
                'status' => 'completed',
            ]);

        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            $exportrecord->update(['status' => 'failed']);
        }
    }
}
