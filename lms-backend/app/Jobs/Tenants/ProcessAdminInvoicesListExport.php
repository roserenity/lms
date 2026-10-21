<?php

namespace App\Jobs\Tenants;

use App\Exports\Tenants\Admin\CourseInvoicesExport;
use App\Exports\Tenants\Teacher\InvoiceExport;
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

class ProcessAdminInvoicesListExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $export_record_id;
    private $search;
    private $type_of_learning;
    private $status;
    private $date_from;
    private $date_to;
    private $user_id;

    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $search, $type_of_learning, $status, $date_from, $date_to, $user_id)
    {
        $this->export_record_id = $export_record_id;
        $this->search = $search;
        $this->type_of_learning = $type_of_learning;
        $this->status = $status;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->user_id = $user_id;
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

            $export = new CourseInvoicesExport(
                $this->export_record_id,
                $this->search,
                $this->type_of_learning,
                $this->status,
                $this->date_from,
                $this->date_to,
                $this->user_id
            );

            $filename = 'exports/invoices/overall_invoices_export_' . now()->format('Ymd_His') . '.csv';

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
