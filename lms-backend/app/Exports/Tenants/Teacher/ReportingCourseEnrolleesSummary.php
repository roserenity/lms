<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\StudentModuleProgress;
use App\Models\ZoomMeetingAttendee;
use App\Models\CustomRegistrationField;
use App\Models\CustomRegistrationPage;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use App\Helpers\FilterHelper;

use Carbon\Carbon;

class ReportingCourseEnrolleesSummary implements FromCollection, Responsable, WithMapping, WithHeadings
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

        $course = Course::where('id', $this->course_id)->first();

        $enrollments = Enrollment::where('course_id', $course->id)
            ->whereHas('user', function($query) use ($searchParts) {
                $query->when($this->search, function ($query) use ($searchParts) {
                    Helper::queryNameSearch($searchParts, $query);
                })->whereHas('enrollments', function($subQuery) {
                    $subQuery->when(($this->date_from || $this->date_to), function ($query) {
                        FilterHelper::queryCreatedAt($query, $this->date_from, $this->date_to);
                    });
                });
            })
            ->with([
                'course' => function ($query) {
                    $query->select(['id', 'title', 'type_of_learning', 'uuid'])
                        ->withCount(['assessments', 'topics', 'sub_topics', 'course_evaluations', 'module_evaluations', 'zoom_meetings', 'group_activities'])
                        ->withCount([
                            'my_assessment_score as total_checked_assessments' => function ($query) {
                                $query->where('checked', true);
                            }
                        ]);
                    },
                'user' => function($query) use ($course) {
                        $query->select(['id', 'first_name', 'last_name', 'data', 'email', 'last_name', 'suffix'])
                            ->with(['completed_course' => fn($completed_course) 
                                => $completed_course->where('course_id', $course->id),
                            'certificates' => fn($certificates) 
                                => $certificates->where('course_id', $course->id)->with('image')->whereHas('image')]);
                    }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($enrollments as $enrollment) {
            $enrollment->course->makeHidden([
                'course_card',
                'image',
                'course_is_open_for_registration',
                'module_evaluations',
                'user_is_enrolled'
            ]);
        
            $course = $enrollment->course; // Access the associated course

            // Access properties and call methods on the associated course
            $course = Helper::setLearnerCourseData($course, $enrollment->user_id);
            $course['total_checked_assessments'] = $course->total_checked_assessments ?? 0;

            // Get the latest content done
            $latestContentDone = StudentModuleProgress::where('course_id', $course->id)
                ->where('user_id', $enrollment->user_id)
                ->done()
                ->latest()
                ->first();

            // Get the latest zoom attended
            $latestZoomAttended = ZoomMeetingAttendee::whereIn('zoom_meeting_id', $course->zoom_meetings()->pluck('id'))
                ->where('present', 1)
                ->where('user_id', $enrollment->user_id)
                ->latest()
                ->first();

            // Parse the creation dates with Carbon
            $latestContentDoneDate = $latestContentDone ? Carbon::parse($latestContentDone->created_at) : null;
            $latestZoomAttendedDate = $latestZoomAttended ? Carbon::parse($latestZoomAttended->created_at) : null;

            // Find the latest activity date
            $latestActivityDate = $latestContentDoneDate && $latestZoomAttendedDate 
            ? $latestContentDoneDate->max($latestZoomAttendedDate) 
            : ($latestContentDoneDate ?: $latestZoomAttendedDate);

            $course['latest_activity_date'] = $latestActivityDate;

            $enrollment['progress'] = $course->type_of_learning === 'BLENDED' ? Helper::calculateLearnerProgressPercentBlended($course) : Helper::calculateLearnerProgressPercentElearning($course);

        }

        return collect($enrollments);
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

        
        /* return [
            $data->user->first_name.' '. $data->user->last_name . ' '. $data->user->suffix,
            $data->user->email,
            $data->progress,
            $data->progress == 100 ? (count($data->user->completed_course) > 0 ? Carbon::parse($data->user->completed_course[0]->created_at)->toDateString() : ( count($data->user->certificates) > 0 ? Carbon::parse($data->user->certificates[0]->created_at)->toDateString() : Carbon::parse($data->course->latest_activity_date)->toDateString() )): '',
            $data->progress == 100 ? (count($data->user->certificates) > 0 ? $data->user->certificates[0]->image->url :'') : '',
            ...$user_data
        ]; */

        return [
            $data->user->full_name,
            $data->user->email,
            $data->progress,
            count($data->user->completed_course) > 0 ? Carbon::parse($data->user->completed_course[0]->created_at)->toDateString() : ($data->progress === 100 ? Carbon::parse($data->course->latest_activity_date)->toDateString() : ''),
            count($data->user->certificates) > 0 ? $data->user->certificates[0]->image->url :'',
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
            'Email',
            'Progress Status (%)',
            'Date Completed',
            'Completion Certificate',
            ...$custom_fields,
        ];
    }
}
