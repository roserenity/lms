<?php

namespace App\Exports\Tenants\Teacher;

use Spatie\Activitylog\Models\Activity;
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

class ActivityLogsExport implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'activity_logs.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $log_name;

    public function __construct($log_name)
    {
        $this->log_name = $log_name;
    }

    public function collection()
    {
        $courses = Course::pluck('id');

        $activity = Activity::latest()
            ->whereIn('properties->course_id', $courses)
            ->when(!empty($this->log_name), function ($query) {
                $query->whereIn('log_name', $this->log_name);
            })->get();

        return collect($activity);
    }

    public function map($data): array
    {
        $course_id = $data->properties['course_id'];

        $course = Course::select(['id', 'title'])->find($course_id)?->toArray();

        return [
            $data->description,
            $course['title'],
            $data->causer?->full_name,
            Carbon::parse($data->created_at)->toDateString(),
            Carbon::parse($data->created_at)->toTimeString()
        ];
    }

    public function headings(): array
    {
        
        return [
            'Activity',
            'Course',
            'Learner',
            'Date',
            'Time'
        ];
    }
}
