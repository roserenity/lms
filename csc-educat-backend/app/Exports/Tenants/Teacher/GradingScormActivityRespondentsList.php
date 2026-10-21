<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use App\Models\ScormActivityScore;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GradingScormActivityRespondentsList implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'grading_scorm_activity_respondents.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $course_id;
    protected $scorm_activity_id;
    protected $submitted_from;
    protected $submitted_to;
    protected $search;

    public function __construct($course_id, $scorm_activity_id, $search, $submitted_from, $submitted_to)
    {
        $this->course_id = $course_id;
        $this->scorm_activity_id = $scorm_activity_id;
        $this->submitted_from = $submitted_from;
        $this->submitted_to = $submitted_to;
        $this->search = $search;
    }

    public function collection()
    {
      $searchParts = explode(' ', $this->search);

      $scorm_scores = ScormActivityScore::where('course_id', $this->course_id)
            ->where('scorm_activity_id', $this->scorm_activity_id)
            ->with('user', function($user) {
                $user->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
            })
            ->whereHas('user', function($query) use ($searchParts){
                $query->when(!empty($this->search), function ($query) use ($searchParts) {
                    Helper::queryNameSearch($searchParts, $query);
                });
            })
            ->when(($this->submitted_from || $this->submitted_to), function ($query) {
                FilterHelper::queryCreatedAt($query, $this->submitted_from, $this->submitted_to);
            })
            ->get();

      return collect($scorm_scores);
    }

    public function map($data): array
    {
        return [
          $data->user->full_name,
          $data->user->email,
          $data->score,
          Carbon::parse($data->created_at)->toDateString(),
          Carbon::parse($data->created_at)->toTimeString(),
        ];
    }

    public function headings(): array
    {
      return [
        'Name',
        'Email',
        'Score',
        'Date Accomplished',
        'Time Accomplished',
      ];
    }
}
