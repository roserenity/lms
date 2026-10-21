<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Admin\TicketsExport;
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

class ProcessAdminTicketsExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $export_record_id;
    private $status;
    private $category;
    private $date_from;
    private $date_to;
    private $search;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $status, $category, $date_from, $date_to, $search)
    {
        $this->export_record_id = $export_record_id;
        $this->status = $status;
        $this->category = $category;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->search = $search;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $exportrecord = Exports::find($this->export_record_id);

        try {

            $export = new TicketsExport(
                $this->export_record_id,
                $this->status,
                $this->category,
                $this->date_from,
                $this->date_to,
                $this->search,
            );

            $filename = 'exports/tickets/tickets_export_' . now()->format('Ymd_His') . '.csv';

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
