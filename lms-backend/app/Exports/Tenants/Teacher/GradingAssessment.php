<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use App\Models\Assessment;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GradingAssessment implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'user_assesment_list.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $assessment_id;
    protected $submitted_from;
    protected $submitted_to;
    protected $search;

    public function __construct($assessment_id, $submitted_from, $submitted_to, $search)
    {
        $this->assessment_id = $assessment_id;
        $this->submitted_from = $submitted_from;
        $this->submitted_to = $submitted_to;
        $this->search = $search;
    }

    public function collection()
    {
      $searchParts = explode(' ', $this->search);

      $assessment = Assessment::where('id', $this->assessment_id)->first();
  
      if (!$assessment) {
        return $this->sendResponse([], 'No assessment found.', 404);
      }

      $assessment = Assessment::query()->select(['id'])->with([
        'assessment_scores' => fn ($score) => $score->with('user')
          ->where('active', 1)
          ->whereHas('user', function ($query) use ($searchParts, $assessment) {
            FilterHelper::filterEnrolledUsers($query, $assessment->course_id);

            $query->when($this->search, function ($subQuery) use ($searchParts) {
              Helper::queryNameSearch($searchParts, $subQuery);
            });
          })
          ->when(($this->submitted_from || $this->submitted_to), function ($query) {
            FilterHelper::queryCreatedAt($query, $this->submitted_from, $this->submitted_to);
          })->get(),
        ])
        ->where('id', $this->assessment_id)
        ->first();    

      return collect($assessment->assessment_scores);
    }

    public function map($data): array
    {
        return [
          $data->user->full_name,
          $data->user->email,
          $data->checked == 1 ? 'checked' : 'pending',
          Carbon::parse($data->created_at)->toDateString(),
          Carbon::parse($data->created_at)->toTimeString(),
          $data->score
        ];
    }

    public function headings(): array
    {
      return [
        'Name',
        'Email',
        'Grading Status',
        'Date Accomplished',
        'Time Accomplished',
        'Score'
      ];
    }
}
