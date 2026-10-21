<?php

namespace App\Exports\Tenants\Teacher;

use App\Models\Enrollment;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use App\Helpers\Helper;


class ZoomMeetingParticipantsSummary implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'zoom_meeting_participants_summary.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $topic;
    protected $course_id;
    protected $zoom_meeting_id;
    protected $search;
    protected $status;

    public function __construct($topic, $course_id, $zoom_meeting_id, $search, $status)
    {
        $this->topic = $topic;
        $this->course_id = $course_id;
        $this->zoom_meeting_id = $zoom_meeting_id;
        $this->search = $search;
        $this->status = $status;
    }

    public function collection()
    {

        $searchParts = explode(' ', $this->search);

        $enrollees = Enrollment::query()->where('course_id', $this->course_id)
            ->has('user')
            ->select('enrollments.*', 'zoom_meeting_attendees.present')
            ->when($this->search, function($query) use ($searchParts) {
                $query->whereHas('user', function ($subQuery)  use ($searchParts)  {
                    Helper::queryNameSearch($searchParts, $subQuery);
                });
            })
            ->when($this->status, function($query) {
                return $query->where('zoom_meeting_attendees.present', 
                    $this->status == 'present' ? 1 : 
                    ($this->status == 'absent' ? 0 : null)
                );
            })
            ->leftJoin('zoom_meeting_attendees', function($join) {
                $join->on('enrollments.user_id', '=', 'zoom_meeting_attendees.user_id')
                    ->where('zoom_meeting_attendees.zoom_meeting_id', '=', $this->zoom_meeting_id);
            })
            ->with('user')
            ->get();

        return collect($enrollees);
    }

    public function map($data): array
    {
        return [
            $this->topic,
            $data->user->first_name,
            $data->user->middle_name,
            $data->user->last_name,
            $data->user->suffix,
            $data->user->email,
            $data->present === 1 ? 'Present' : ($data->present === 0 ? 'Absent' : 'Pending')
        ];
    }

    public function headings(): array
    {
        return [
            'Zoom Meeting Topic',
            'First Name',
            'Middle Name',
            'Last Name',
            'Suffix',
            'Email',
            'Attendance'
        ];
    }
}
