<?php

namespace App\Exports\Tenants\Admin;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use App\Models\Invoice;
use App\Models\Exports;
use App\Models\Course;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Events\AfterSheet;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;

class CourseInvoicesExport implements FromQuery, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'invoices.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $export_record_id;
    protected $search;
    protected $type_of_learning;
    protected $status;
    protected $created_from;
    protected $created_to;

    public function __construct($export_record_id, $search, $type_of_learning, $status, $created_from, $created_to)
    {
        $this->export_record_id = $export_record_id;
        $this->search = $search;
        $this->type_of_learning = $type_of_learning;
        $this->status = $status;
        $this->created_from = $created_from;
        $this->created_to = $created_to;
    }

    public function afterWriting(AfterSheet $event) 
    {

        $exportRecord = Exports::find($this->export_record_id);
        
        $exportRecord->update([
            'status' => 'completed',
        ]);

        $exportRecord->save();
    }

    
    public function chunkSize(): int
    {
        return 1000; // adjust as needed
    }

    public function query()
    {

        return Course::query()
            ->select(['title', 'status', 'type_of_learning', 'created_at', 'course_code'])
            ->when($this->search, function ($query) {
                FilterHelper::queryCourseTitleAndCodeSearch($this->search, $query);
            })
            ->when($this->type_of_learning, function ($query) {
                $query->where('type_of_learning', $this->type_of_learning);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status );
            })
            ->when(($this->created_from || $this->created_to), function ($query) {
                FilterHelper::queryCreatedAt($query, $this->created_from, $this->created_to);
            })
            ->withCount([
                'invoices as pending_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_PENDING);
                },
                'invoices as success_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_SUCCESS);
                },
                'invoices as fail_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_FAILED);
                },
                'invoices as cancel_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_CANCELLED);
                },
                'invoices as commitment_letters' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_COMMITMENT_LETTER);
                },
                'invoices as free_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_FREE);
                },
                'invoices as disapproved_invoices' => function ($query) {
                    $query->where('status', Invoice::PAYMENT_DISAPPROVED);
                }
            ]);
    }

    public function map($data): array
    {

        return [
            $data->title,
            $data->course_code,
            $data->type_of_learning,
            $data->status.'ED',
            Carbon::parse($data->created_at)->toDateString(),
            Carbon::parse($data->created_at)->toTimeString(),
            $data->free_invoices,
            $data->pending_invoices,
            $data->success_invoices,
            $data->commitment_letters,
            $data->fail_invoices,
            $data->disapproved_invoices,
            $data->cancel_invoices,
        ];
    }

    public function headings(): array
    {
        
        return [
            'Title',
            'Code',
            'Type',
            'Status',
            'Date Created',
            'Time Created',
            'Free',
            'Pending',
            'Successful',
            'Commitment Letter',
            'Disqualified',
            'Disapproved',
            'Cancelled'
        ];
    }
}
