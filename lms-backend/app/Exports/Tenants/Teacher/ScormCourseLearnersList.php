<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Models\Scorm;
use App\Models\ScormRegistration;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use Carbon\Carbon;

class ScormCourseLearnersList implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'scorm_learners_list.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $scorm_id;
    protected $search;

    public function __construct($scorm_id, $search)
    {
        $this->scorm_id = $scorm_id;
        $this->search = $search;
    }

    public function collection()
    {
      $searchParts = explode(' ', $this->search);

      $scorm = Scorm::find($this->scorm_id);

      $scorm_registration = ScormRegistration::where('scorm_id', $this->scorm_id)->with(['scorm_progress', 'user' => function($query) {
              $query->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
          }])
          ->whereHas('user', function($query) use ($searchParts, $scorm){
            FilterHelper::filterEnrolledUsers($query, $scorm->course_id);
            $query->when(!empty($request->search), function ($query) use ($searchParts) {
                Helper::queryNameSearch($searchParts, $query);
            });
          })->get();

      return collect($scorm_registration);
    }

    public function map($data): array
    {
        return [
          $data->user->full_name,
          $data->user->email,
          $data->scorm_progress->completion_status ?? '',
          /* $data->scorm_progress->success_status ?? '', */
          $data->scorm_progress->total_seconds_tracked ?? '',
          $data->scorm_progress?->completion_date ? Carbon::parse($data->scorm_progress->completion_date)->toDateString() : '',
        ];
    }

    public function headings(): array
    {
      return [
        'NAME',
        'EMAIL',
        'COMPLETED',
        /* 'SUCCESS', */
        'TIME SPENT (seconds)',
        'COMPLETION DATE'
      ];
    }
}
