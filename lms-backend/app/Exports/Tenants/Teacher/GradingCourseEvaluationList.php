<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;

use App\Models\User;
use App\Models\CourseEvaluation;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use App\Http\Resources\Tenants\Teacher\StudentCourseEvaluationResource;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GradingCourseEvaluationList implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'user_course_evaluation_list.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $course_evaluation_id;
    protected $submitted_from;
    protected $submitted_to;
    protected $search;

    public function __construct($course_evaluation_id, $submitted_from, $submitted_to, $search)
    {
        $this->course_evaluation_id = $course_evaluation_id;
        $this->submitted_from = $submitted_from;
        $this->submitted_to = $submitted_to;
        $this->search = $search;
    }

    public function collection()
    {
      $searchParts = explode(' ', $this->search);

      $courseEvaluation = CourseEvaluation::select(['id', 'course_id'])->find($this->course_evaluation_id);

      if (!$courseEvaluation) {
          return $this->sendResponse([], 'No course evaluation found.', 404);
      }

      $users = User::select(['id', 'first_name', 'last_name', 'suffix', 'email', 'uuid'])->when($this->search, function ($query) use ($searchParts) {
            Helper::queryNameSearch($searchParts, $query);
        })
        ->whereHas('enrollments', function($query) use ($courseEvaluation) {
            $query->where('course_id', $courseEvaluation->course_id);
        })
        ->whereHas('student_course_evaluations', function ($subquery) {
            $subquery->where('course_evaluation_id', $this->course_evaluation_id)
                ->when(($this->submitted_from || $this->submitted_to), function ($query) {
                    FilterHelper::queryCreatedAt($query, $this->submitted_from, $this->submitted_to);
                });
        })
        ->with([
            'student_course_evaluations' => function ($student_course_evaluations) {
                return $student_course_evaluations
                    ->where('course_evaluation_id', $this->course_evaluation_id)
                    ->orderBy('id', 'DESC')
                    ->get();
            }
        ])->get();

        $usersResource = StudentCourseEvaluationResource::collection($users);

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
