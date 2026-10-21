<?php

namespace App\Exports\Tenants\Teacher;

use App\Models\Course;
use App\Models\StudentCompleteCourse;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use App\Helpers\FilterHelper;
use Carbon\Carbon;

class ReportingCoursesSummary implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'reporting_courses_summary.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $id;
    protected $search;
    protected $date_from;
    protected $date_to;
    protected $status;
    protected $type_of_learning;
    protected $enrolled_from;
    protected $enrolled_to;

    public function __construct($search, $id, $date_from, $date_to, $status, $type_of_learning, $enrolled_from, $enrolled_to)
    {
        $this->id = $id;
        $this->search = $search;
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->status = $status;
        $this->type_of_learning = $type_of_learning;
        $this->enrolled_from = $enrolled_from;
        $this->enrolled_to = $enrolled_to;
    }

    public function collection()
    {
        $courses = Course::query()
            ->select(['id', 'title', 'uploader_id', 'status', 'available_until', 'type_of_learning', 'training_hours', 'course_code', 'created_at'])
            ->where('status', '!=', COURSE::IS_DRAFT)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('uploader_id', $this->id)
                    ->orWhereRelation('shared_to_users', 'user_id', $this->id);
                });
            })
            ->when($this->search, function ($query) {
                FilterHelper::queryCourseTitleAndCodeSearch($this->search, $query);
            })
            ->when(($this->date_from || $this->date_to), function ($query) {
                FilterHelper::queryAvailabilityDates($query, $this->date_from, $this->date_to);
            })
            ->when($this->status, function ($query) {
                $query->whereIn('status', $this->status ? [$this->status] : [ Course::IS_PUBLISH, COURSE::IS_UNPUBLISH ]);
            })
            ->when($this->type_of_learning, function ($query) {
                $query->where('type_of_learning', $this->type_of_learning);
            })
            ->withCount(['enrollments' => function($query) {
                $query->whereHas('user')
                    ->when(($this->enrolled_from || $this->enrolled_to), function ($query) {
                        FilterHelper::queryCreatedAt($query, $this->enrolled_from, $this->enrolled_to);
                    });
                },
                'completed_course',
            ])
            ->get();

        $courses->each(function($course){
            $course->completed_course_count = StudentCompleteCourse::where('course_id', $course->id)
                ->has('user')
                ->whereHas('user.enrollments', function($query) use ($course){
                    $query->where('course_id', $course->id);
                })
                ->distinct('user_id')
                ->get()
                ->count();
        });

        return collect($courses);
    }

    public function map($data): array
    {
        return [
            $data->title,
            $data->course_code,
            $data->status.'ED',
            $data->type_of_learning,
            $data->enrollments_count,
            $data->completed_course_count,
            $data->training_hours,
            $data->available_until ? ($data->available_until['start_date'] ? $data->available_until['start_date'] : '' ) : '',
            $data->available_until ? ($data->available_until['end_date'] ? $data->available_until['end_date'] : '' ) : '',
            Carbon::parse($data->created_at)->toDateString(),
        ];
    }

    public function headings(): array
    {
        return [
            'COURSE',
            'COURSE CODE',
            'STATUS',
            'TYPE OF LEARNING',
            'ENROLLED USERS',
            'COMPLETERS',
            'TRAINING HOURS',
            'DATE CONDUCTED',
            'END',
            'CREATED AT'
        ];
    }
}
