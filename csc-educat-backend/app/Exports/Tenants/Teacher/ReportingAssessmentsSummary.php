<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Enrollment;
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

class ReportingAssessmentsSummary implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'reporting_courses_summary.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $course_id;
    protected $search;

    public function __construct($search, $course_id)
    {
        $this->course_id = $course_id;
        $this->search = $search;
    }

    public function collection()
    {
        $assessments = Assessment::query()->select(['id', 'title', 'course_id'])
                ->where('course_id', $this->course_id)
                ->when($this->search, function ($query) {
                    $query->where('title', 'LIKE', '%' . $this->search . '%');
                })
                ->withCount([
                    'assessment_scores as passed_count' => function($query){
                        $query->where('status', AssessmentScore::IS_PASSED)->where('active', 1);
                    },
                    'assessment_scores as failed_count' => function($query){
                        $query->where('status', AssessmentScore::IS_FAILED)->where('active', 1);
                    },
                    'assessment_scores as answered_count' => function($query){
                        $query->where('active', 1);
                    },
                ])
                ->get();

        return collect($assessments);
    }

    public function map($data): array
    {

        $enrolled_count = Enrollment::where('course_id', $this->course_id)->whereHas('user')->count();

        return [
            $data->title,
            $data->passed_count,
            $data->failed_count,
            $data->answered_count,
            $enrolled_count - $data->answered_count
        ];
    }

    public function headings(): array
    {   
        return [
            'Assessment Title',
            'Passers',
            'Failures', 
            'Answered',
            'Unanswered'
        ];
    }
}
