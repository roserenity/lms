<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Admin\DashboardCourseExport;
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

class ProcessAdminCourseExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $export_record_id;
    private $filter;
    private $search;
    private $date_from;
    private $date_to;
    private $status;
    private $type_of_learning;
    private $created_from;
    private $created_to;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $filter, $search, $date_from, $date_to, $status, $type_of_learning, $created_from, $created_to)
    {
        $this->export_record_id = $export_record_id;
        $this->filter = $filter;
        $this->search = $search;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->status = $status;
        $this->type_of_learning = $type_of_learning;
        $this->created_from = $created_from;
        $this->created_to = $created_to;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $exportrecord = Exports::find($this->export_record_id);

        try {

            $export = new DashboardCourseExport(
                $this->export_record_id,
                $this->filter,
                $this->search,
                $this->date_from,
                $this->date_to,
                $this->status,
                $this->type_of_learning,
                $this->created_from,
                $this->created_to
            );
            
            $filename = 'exports/course/LMS-courses_' . now()->format('Ymd_His') . '.csv';

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
