<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use App\Models\User;
use App\Models\StudentCompleteCourse;
use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use Carbon\Carbon;

class ReportingCourseCompletersSummary implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'reporting_courses_summary.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $course_id;
    protected $search;
    protected $date_from;
    protected $date_to;

    public function __construct($search, $course_id, $date_from, $date_to)
    {
        $this->course_id = $course_id;
        $this->search = $search;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
    }

    public function collection()
    {
        $searchParts = explode(' ', $this->search);

        $users =  StudentCompleteCourse::query()->where('course_id', $this->course_id)
            ->whereHas('user', function($query) {
                $query->whereHas('enrollments', function($subQuery) {
                    $subQuery->when(($this->date_from || $this->date_to), function ($query) {
                        FilterHelper::queryCreatedAt($query, $this->date_from, $this->date_to);
                    });
                });
            })
            ->with('user', function($user) {
                $user->with('enrollments', function ($query) {
                    $query->where('course_id', $this->course_id);
                });
            })
            ->when(!empty($this->search), function ($query) use ($searchParts) {
                $query->whereHas('user', function ($query) use ($searchParts) {
                    Helper::queryNameEmailSearch($searchParts, $query);
                });
            })
            ->distinct('user_id')
            ->get();

        return collect($users);
    }

    public function map($data): array
    {
        $custom_registration_pages = CustomRegistrationPage::all();
        $user_data = [];
        foreach($custom_registration_pages as $page) {
            $labels = CustomRegistrationField::where('custom_registration_page_id', $page->id)->get();
            foreach($labels as $label) {
                if ($data->user->data !== []) {
                    
                    $value = $data->user->data[$page->title][$label->input_field_name] ?? '';

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
            }
        }
        
        return array_merge([
            !empty($data->user->enrollments) && isset($data->user->enrollments[0]) ?Carbon::parse($data->user->enrollments[0]->created_at)->toDateString() : '',
            $data->user->first_name,
            $data->user->middle_name,
            $data->user->last_name,
            $data->user->suffix,
            $data->user->email,
            $data->user->status
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

        return array_merge([
            'Enrollment Date',
            'First Name',
            'Middle Name',
            'Last Name',
            'Suffix',
            'Email',
            'Status',
        ], $custom_fields);
    }
}
