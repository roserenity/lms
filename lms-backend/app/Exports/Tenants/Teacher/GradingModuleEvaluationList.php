<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use App\Models\User;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use App\Http\Resources\Tenants\Teacher\StudentModuleEvaluationResource;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GradingModuleEvaluationList implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'user_module_evaluation_list.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $module_evaluation_id;
    protected $submitted_from;
    protected $submitted_to;
    protected $search;
    protected $course_id;

    public function __construct($module_evaluation_id, $submitted_from, $submitted_to, $search, $course_id)
    {
        $this->module_evaluation_id = $module_evaluation_id;
        $this->submitted_from = $submitted_from;
        $this->submitted_to = $submitted_to;
        $this->search = $search;
        $this->course_id = $course_id;
    }

    public function collection()
    {
      $searchParts = explode(' ', $this->search);

      $users = User::select(['id', 'first_name', 'last_name', 'suffix', 'email', 'uuid'])->when($this->search, function ($query) use ($searchParts) {
            Helper::queryNameSearch($searchParts, $query);
        })
        ->whereHas('enrollments', function($query) {
            $query->where('course_id', $this->course_id);
        })
        ->whereHas('student_module_evaluations', function ($subquery) {
            $subquery->where('module_evaluation_id', $this->module_evaluation_id)
                ->when(($this->submitted_from || $this->submitted_to), function ($query) {
                    FilterHelper::queryCreatedAt($query, $this->submitted_from, $this->submitted_to);
                });
        })
        ->with([
            'student_module_evaluations' => function ($student_module_evaluations) {
                return $student_module_evaluations
                    ->where('module_evaluation_id', $this->module_evaluation_id)
                    ->orderBy('id', 'DESC')
                    ->get();
            }
        ])->get();

        $usersResource = StudentModuleEvaluationResource::collection($users);

        return collect($usersResource);
    }

    public function map($data): array
    {
        return [
          $data['name'],
          $data['email'],
          $data['status'] == 1 ? 'checked' : 'pending',
          Carbon::parse($data['date_submitted'])->toDateString(),
          Carbon::parse($data['date_submitted'])->toTimeString(),
          $data['attempts'],
        ];
    }

    public function headings(): array
    {
      return [
        'Name',
        'Email',
        'Evaluation Status',
        'Date Accomplished',
        'Time Accomplished',
        'Attempts'
      ];
    }
}
