<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use App\Models\Invoice;
use App\Models\Course;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Illuminate\Support\Str;

class CourseInvoicesExport implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'invoices.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $search;
    protected $type_of_learning;
    protected $status;
    protected $date_from;
    protected $date_to;
    protected $uploader_id;

    public function __construct($search, $type_of_learning, $status, $date_from, $date_to, $uploader_id)
    {
        $this->search = $search;
        $this->type_of_learning = $type_of_learning;
        $this->status = $status;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->uploader_id = $uploader_id;
    }

    public function collection()
    {

        $courses = Course::query()
            ->select(['title', 'uploader_id', 'status', 'type_of_learning', 'available_until', 'course_code'])
            ->with(['shared_to_users'])
            ->when($this->search, function ($query) {
                FilterHelper::queryCourseTitleAndCodeSearch($this->search, $query);
            })
            ->when($this->status, function ($query) {
                $query->whereIn('status', $this->status ? [$this->status] : [ Course::IS_PUBLISH, COURSE::IS_UNPUBLISH ]);
            })
            ->when($this->type_of_learning, function ($query) {
                $query->where('type_of_learning', $this->type_of_learning);
            })
            ->when(($this->date_from || $this->date_to), function ($query) {
                FilterHelper::queryAvailabilityDates($query, $this->date_from, $this->date_to);
            })
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('uploader_id', $this->uploader_id)
                    ->orWhereRelation('shared_to_users', 'user_id', $this->uploader_id);
                });
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
            ])
            ->latest()
            ->get();

        return collect($courses);
    }

    public function map($data): array
    {

        return [
            $data->title,
            $data->course_code,
            $data->type_of_learning,
            $data->title,
            $data->available_until ? ($data->available_until['start_date'] ? $data->available_until['start_date'] : '' ) : '',
            $data->available_until ? ($data->available_until['end_date'] ? $data->available_until['end_date'] : '' ) : '',
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
            'COURSE TITLE',
            'COURSE CODE',
            'STATUS',
            'TYPE OF LEARNING',
            'DATE CONDUCTED START',
            'DATE CONDUCTED END',
            'FREE',
            'PENDING',
            'SUCCESSFUL',
            'COMMITMENT LETTER',
            'DISQUALIFIED',
            'DISAPPROVED',
            'CANCELLED'
        ];
    }
}
