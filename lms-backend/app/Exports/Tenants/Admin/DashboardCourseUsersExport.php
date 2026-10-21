<?php

namespace App\Exports\Tenants\Admin;

use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;
use App\Models\User;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use Carbon\Carbon;
use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Models\Exports;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use Log;

class DashboardCourseUsersExport implements FromQuery, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'dashboard_course_users.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $export_record_id;
    protected $filter;
    protected $value;
    protected $field;
    protected $search;
    protected $date_from;
    protected $date_to;

    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [$this, 'afterWriting']
        ];
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

    public function __construct($export_record_id, $filter, $value, $field, $search, $date_from, $date_to)
    {
        $this->export_record_id = $export_record_id;
        $this->filter = $filter;
        $this->value = $value;
        $this->field = $field;
        $this->search = $search;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
    }

    public function query()
    {
        $users = [];

        $searchParts = explode(' ', $this->search);

        if ($this->filter == 'Total Users per Course') {

            return User::query()->whereHas('enrollments', function ($query) {
                $query->where('course_id', $this->value)
                    ->when($this->date_from && $this->date_to, function ($query) {
                        $query->whereDate('created_at', '>=', $this->date_from)->whereDate('created_at', '<=', $this->date_to);
                    });
                })
                ->with('enrollments', function ($query) {
                    $query->where('course_id', $this->value)
                        ->when(($this->date_from || $this->date_to), function ($query) {
                                FilterHelper::queryCreatedAt($query, $this->date_from, $this->date_to);
                        });
                })
                ->when(!empty($this->search), function ($query) use ($searchParts) {
                    Helper::queryNameEmailSearch($searchParts, $query);
                });

        } else {

            $custom_field = CustomRegistrationField::where('input_field_label', $this->filter)->first();
            $custom_page = CustomRegistrationPage::where('id', $custom_field->custom_registration_page_id)->first();

            return User::query()->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $this->field)
                ->when(!empty($this->search), function ($query) use ($searchParts) {
                    Helper::queryNameEmailSearch($searchParts, $query);
                })
                ->whereHas('enrollments', function($enrollments) {
                    $enrollments->when(($this->date_from || $this->date_to), function ($query) {
                        FilterHelper::queryCreatedAt($query, $this->date_from, $this->date_to);
                    });
                });
        }

        return $users;
    }

    public function map($data): array
    {
        $custom_registration_pages = CustomRegistrationPage::all();
        $user_data = [];
        foreach($custom_registration_pages as $page) {
            $labels = CustomRegistrationField::where('custom_registration_page_id', $page->id)->get();
            foreach($labels as $label) {
                if ($data->data !== []) {
                    
                    if (isset($data->data[$page->title], $data->data[$page->title][$label->input_field_name])) {
                        $value = $data->data[$page->title][$label->input_field_name] ?? '';

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
        
        return array_merge([
            Carbon::parse($data->enrollments[0]->created_at)->toDateString(),
            $data->first_name,
            $data->middle_name,
            $data->last_name,
            $data->suffix,
            $data->email,
            $data->status
        ], $user_data);
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
        //$custom_fields = CustomRegistrationField::where('input_field_type', 'dropdown')->get()->pluck(['input_field_label'])->toArray();

        $fields = [
            'First Name',
            'Middle Name',
            'Last Name',
            'Suffix',
            'Email',
            'Status',
        ];
        
        return array_merge(
            ($this->filter === 'Total Users per Course') ? ['Enrollment Date', ...$fields, ...$custom_fields] : [...$fields, ...$custom_fields]
        );
    }
}
