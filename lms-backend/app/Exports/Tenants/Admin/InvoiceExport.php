<?php

namespace App\Exports\Tenants\Admin;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;
use App\Models\Invoice;
use App\Models\Course;
use App\Models\Exports;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Illuminate\Support\Str;

class InvoiceExport implements FromQuery, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'invoices.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $export_record_id;
    protected $course_uuid;
    protected $invoice_status;
    protected $mode_of_payment;
    protected $type_of_payment;
    protected $date_from;
    protected $date_to;
    protected $search;
    protected $enrolled_by;
    protected $enrollment_status;
    protected $receipt_pick_up_option;

    public function __construct($export_record_id, $course_uuid, $invoice_status, $mode_of_payment, $type_of_payment, $date_from, $date_to, $search, $enrolled_by, $enrollment_status, $receipt_pick_up_option)
    {
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
        
        $searchParts = explode(' ', $this->search);
        $searchParts2 = explode(' ', $this->enrolled_by);

        $course = Course::where('uuid', $this->course_uuid)->first();

        return Invoice::query()->with([
                'enroll_by', 
                'nomination_form', 
                'image', 
                'proof_of_payment', 
                'commitment_letter',
                'prime_resolution',
                'endorsement_letter',
                'enrollee' => function($query) use ($course) {
                    $query->select(['id', 'first_name', 'last_name', 'suffix', 'email', 'uuid', 'data'])
                        ->with(['enrollments' => function($subQuery) use ($course) {
                            $subQuery->where('course_id', $course->id);
                        }]);
                }
            ])
            ->where('course_id', $course->id)
            ->when($this->invoice_status, function ($query) {
                $query->where('status', $this->invoice_status);
            })
            ->when($this->mode_of_payment, function ($query) {
                $query->where('mode_of_payment', $this->mode_of_payment);
            })
            ->when($this->type_of_payment, function ($query) {
                $query->where('type_of_payment', $this->type_of_payment);
            })
            ->when($this->receipt_pick_up_option, function ($query) {
                $query->where('receipt_pick_up_option', $this->receipt_pick_up_option);
            })
            ->when(($this->date_from || $this->date_to), function ($query) {
                FilterHelper::queryCreatedAt($query, $this->date_from, $this->date_to);
            })
            ->when($this->search, function ($query) use ($searchParts) {
                $query->whereHas('enrollee', function ($innerQuery) use ($searchParts) {
                    Helper::queryNameEmailSearch($searchParts, $innerQuery);
                });
            })
            ->when($this->enrolled_by, function ($query) use ($searchParts2) {
                $query->whereHas('enroll_by', function ($innerQuery) use ($searchParts2) {
                    Helper::queryNameSearch($searchParts2, $innerQuery);
                });
            })
            ->when($this->enrollment_status == 2, function ($query) use ($course) {
                $query->whereHas('enrollee', function ($innerQuery) use ($course) {
                    $innerQuery->whereDoesntHave('enrollments', function ($subQuery) use ($course) {
                        $subQuery->where('course_id', $course->id);
                    });
                });
            })
            ->when($this->enrollment_status == 1, function ($query) use ($course) {
                $query->whereHas('enrollee', function ($innerQuery) use ($course) {
                    $innerQuery->whereHas('enrollments', function ($subQuery) use ($course) {
                        $subQuery->where('course_id', $course->id);
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->whereHas('enrollee');
    }

    public function map($data): array
    {
        $custom_registration_pages = CustomRegistrationPage::all();
        $user_data = [];
        foreach($custom_registration_pages as $page) {
            $labels = CustomRegistrationField::where('custom_registration_page_id', $page->id)->get();
            foreach($labels as $label) {
                if ($data->enrollee->data !== []) {
                    if (isset($data->enrollee->data[$page->title], $data->enrollee->data[$page->title][$label->input_field_name])) {
                        $value = $data->enrollee->data[$page->title][$label->input_field_name] ?? '';

                        if ($label->input_field_type == 'dropdown' && $label->is_multiple) {
                            if (is_string($value)) {
                                $decoded = json_decode($value, true);
                            } else {
                                $decoded = $value;
                            }
                        
                            $user_data[] = is_array($decoded) ? implode(', ', $decoded) : (string) $decoded;
                        } else {
                            $user_data[] = is_array($value) ? implode(', ', $value) : $value;
                        }
                    } else {
                        array_push($user_data, '');
                    }
                } else {
                    array_push($user_data, '');
                }
            }
        }

        
        return [
            $data->mode_of_payment,
            $data->reference_id,
            $data->enrollee->first_name.' '. $data->enrollee->last_name . ' '. $data->enrollee->suffix,
            $data->enrollee->email,
            $data->enrollee->data['More Details']['contactnumber'] ?? '', //hardcoded lolololol
            $data->enroll_by ? $data->enroll_by->first_name.' '. $data->enroll_by->last_name . ' '. $data->enroll_by->suffix : '',
            Carbon::parse($data->created_at)->toDateString(),
            Carbon::parse($data->created_at)->toTimeString(),
            Str::replace('_', ' ', strtoupper($data->type_of_participant)),
            Str::replace('_', ' ', strtoupper($data->type_of_payment)),
            $data->nomination_form ? $data->nomination_form->url :'',
            $data->prime_resolution ? $data->prime_resolution->url :'',
            $data->endorsement_letter ? $data->endorsement_letter->url :'',
            $data->commitment_letter ? $data->commitment_letter->url :'',
            $data->proof_of_payment ? $data->proof_of_payment->url :'',
            $data->receipt_pick_up_option ? $data->receipt_pick_up_option : '',
            $data->receipt_pick_up_address ? $data->receipt_pick_up_address : '',
            $data->status ? Invoice::PAYMENT_STATUS_LABELS[$data->status] : '',
            $data->enrollee->enrollments->count() > 0 ? 'ENROLLED' : '',
            ...$user_data
        ];
    }

    public function headings(): array
    {
        $custom_registration_pages = CustomRegistrationPage::all();
        $custom_fields = [];
        foreach($custom_registration_pages as $page) {
            $labels = CustomRegistrationField::where('custom_registration_page_id', $page->id)->get()->pluck(['input_field_label'])->toArray();
            foreach($labels as $label) {
                array_push($custom_fields, $label);
            }
        }
        
        return [
            'Mode of Payment',
            'Reference ID',
            'User',
            'Email',
            'Contact Number',
            'Enrolled By',
            'Transaction Date',
            'Transaction Time',
            'Type of Participant',
            'Type of Payment',
            'Nomination Form',
            'Prime Resolution',
            'Endorsement Letter',
            'Commitment Letter',
            'Proof of Payment',
            'Delivery Method',
            'Delivery Address',
            'Payment Status',
            'Enrollment Status',
            ...$custom_fields
        ];
    }
}
