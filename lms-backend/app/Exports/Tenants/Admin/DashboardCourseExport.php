<?php

namespace App\Exports\Tenants\Admin;

use App\Models\Course;
use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;
use App\Models\User;
use App\Models\StudentCompleteCourse;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
use App\Helpers\FilterHelper;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardCourseExport implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'dashboard_courses.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $export_record_id;
    protected $filter;
    protected $search;
    protected $date_from;
    protected $date_to;
    protected $status;
    protected $type_of_learning;
    protected $created_from;
    protected $created_to;

    public function __construct($export_record_id, $filter, $search, $date_from, $date_to, $status, $type_of_learning, $created_from, $created_to)
    {
        $this->export_record_id = $export_record_id;
        $this->filter = $filter;
        $this->search = $search;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->status = $status;
        $this->type_of_learning = $type_of_learning;
        $this->created_from = $created_from;
        $this->created_to = $created_to;
    }

    public function collection()
    {
        $data = [];
        if ($this->filter === 'Total Users per Course') {
            $data = Course::select(['id', 'status', 'uploader_id', 'title', 'status', 'type_of_learning', 'created_at', 'available_until', 'course_code'])
                
                ->when($this->search, function ($query) {
                    FilterHelper::queryCourseTitleAndCodeSearch($this->search, $query);
                })
                ->when($this->status, function ($query) {
                    $query->whereIn('status', $this->status ? [$this->status] : [ Course::IS_PUBLISH, COURSE::IS_UNPUBLISH ]);
                })
                ->when($this->type_of_learning, function ($query) {
                    $query->where('type_of_learning', $this->type_of_learning);
                })
                ->withCount(['enrollments as total_enrollments' => function($enrollments){
                        $enrollments->has('user');
                    },
                ])
                ->when(($this->created_from || $this->created_to), function ($query) {
                    FilterHelper::queryAvailabilityDates($query, $this->created_from, $this->created_to);
                })
                ->latest()
                ->get();
            
            $data->each(function($course){
                $course->completed_course_count = StudentCompleteCourse::where('course_id', $course->id)
                    ->has('user')
                    ->whereHas('user.enrollments', function($query) use ($course){
                        $query->where('course_id', $course->id);
                    })
                    ->distinct('user_id')
                    ->get()
                    ->count();
            });

        } else {
            $custom_field = CustomRegistrationField::where('input_field_label', $this->filter)->first();
            $custom_page = CustomRegistrationPage::where('id', $custom_field->custom_registration_page_id)->first();
            foreach ($custom_field->data as $value) {
                array_push($data, [
                    'title' => $value,
                    'users_count' => User::query()->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $value)
                        ->whereHas('enrollments', function($query) {
                            $query->when(($this->date_from || $this->date_to), function ($query) {
                                FilterHelper::queryCreatedAt($query, $this->date_from, $this->date_to);
                            });
                        })
                        ->count(),
                    'course_complete_count' => User::query()->where('data->' . $custom_page->title . '->' . $custom_field->input_field_name, $value)
                        ->whereHas('completed_course', function($query) {
                            $query->when(($this->date_from || $this->date_to), function ($query) {
                                FilterHelper::queryCreatedAt($query, $this->date_from, $this->date_to);
                            });
                        })
                        ->count()
                ]);
            }
        }

        return collect($data);
    }

    public function map($data): array
    {
        if ($this->filter === 'Total Users per Course') {
            return [
                $data->title,
                $data->course_code,
                $data->uploader->full_name,
                $data->status,
                ($data->available_until && $data->available_until['start_date']) ? $data->available_until['start_date'] : '',
                ($data->available_until && $data->available_until['end_date']) ? $data->available_until['end_date'] : '',
                $data->total_enrollments,
                $data->completed_course_count,
            ];
        } else {
            return [
                $data['title'],
                $data['users_count'],
                $data['course_complete_count']
            ];
        }
    }

    public function headings(): array
    {

        $date_from = date('M j, Y', strtotime($this->date_from));
        $date_to = date('M j, Y', strtotime($this->date_to));

        if ($this->filter === 'Total Users per Course') {
            return [
                'Course',
                'Course Code',
                'Instructor',
                'Status',
                'Conducted from',
                'Conducted to',
                'Total Enrolled Learners',
                'Total Completers',
            ];
        } else {
            return [
                $this->filter,
                'Enrolled Learners from ' . $date_from .' to ' . $date_to,
                'Completers from ' . $date_from .' to ' . $date_to
            ];
        }
    }
}
