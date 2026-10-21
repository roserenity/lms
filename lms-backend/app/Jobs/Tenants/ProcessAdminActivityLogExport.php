<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Admin\ActivityLogsExport;
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

class ProcessAdminActivityLogExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $export_record_id;
    private $log_name;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $log_name)
    {
        $this->export_record_id = $export_record_id;
        $this->log_name = $log_name;
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

            $export = new ActivityLogsExport(
                $this->log_name,
            );

            $filename = 'exports/logs/activity_logs_' . now()->format('Ymd_His') . '.csv';

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
