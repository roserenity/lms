<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;

use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;
use App\Models\Course;
use App\Models\User;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Illuminate\Support\Str;

class CourseEnrolleesExport implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'enrollees.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $course_uuid;
    protected $search;

    public function __construct($search, $course_uuid)
    {
        $this->course_uuid = $course_uuid;
        $this->search = $search;
    }

    public function collection()
    {
        
        $searchParts = explode(' ', $this->search);

        $course = Course::query()->where('uuid', $this->course_uuid)->first();

        $users = User::query()->whereHas('enrollments', function ($query) use ($course){
                $query->where('course_id', $course->id);
            })
            ->when(!empty(request('search')), function ($query) use ($searchParts) {
                Helper::queryNameEmailSearch($searchParts, $query);
            })
            ->with(['course_progress' => function ($query) use ($course){
                $query->where('course_id', $course->id);
            }])
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

                if ($data->data !== []) {
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
            }
        }
        
        return [
            $data->first_name.' '. $data->last_name . ' '. $data->suffix,
            count($data->course_progress) > 0 ? $data->course_progress[0]->progress : 0,
            $data->email,
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
            'Name',
            'Progress',
            'Email',
            ...$custom_fields
        ];
    }
}
