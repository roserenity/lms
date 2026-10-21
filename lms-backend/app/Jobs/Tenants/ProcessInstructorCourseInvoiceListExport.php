<?php

namespace App\Jobs\Tenants;

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
use Illuminate\Support\Str;

use Log;

class ProcessInstructorCourseInvoiceListExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $export_record_id;
    private $course_uuid;
    private $invoice_status;
    private $mode_of_payment;
    private $type_of_payment;
    private $date_from;
    private $date_to;
    private $search;
    private $enrolled_by;
    private $enrollment_status;
    private $receipt_pick_up_option;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($export_record_id, $course_uuid, $invoice_status, $mode_of_payment, $type_of_payment, $date_from, $date_to, $search, $enrolled_by, $enrollment_status, $receipt_pick_up_option)
    {
        //
        $this->export_record_id = $export_record_id;
        $this->course_uuid = $course_uuid;
        $this->invoice_status = $invoice_status;
        $this->mode_of_payment = $mode_of_payment;
        $this->type_of_payment = $type_of_payment;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->search = $search;
        $this->enrolled_by = $enrolled_by;
        $this->enrollment_status = $enrollment_status;
        $this->receipt_pick_up_option = $receipt_pick_up_option;
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

        $course = Course::where('uuid', $this->course_uuid)->first();

        try {

            $export = new InvoiceExport(
                $this->export_record_id,
                $this->course_uuid,
                $this->invoice_status,
                $this->mode_of_payment,
                $this->type_of_payment,
                $this->date_from,
                $this->date_to,
                $this->search,
                $this->enrolled_by,
                $this->enrollment_status,
                $this->receipt_pick_up_option
            );

            $filename = 'exports/invoices/'. Str::limit(Str::slug($course->title, '_'), 50, '') . '_' . now()->format('Ymd_His') . '.csv';

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
