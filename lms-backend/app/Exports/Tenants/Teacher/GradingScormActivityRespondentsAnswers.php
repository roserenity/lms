<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Models\ScormActivityAnswer;
use App\Models\ScormActivityScore;
use App\Models\ScormQuestion;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GradingScormActivityRespondentsAnswers implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'grading_scorm_activity_respondents_answers.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $course_id;
    protected $scorm_activity_id;
    protected $submitted_from;
    protected $submitted_to;
    protected $search;
    protected $questions;

    public function __construct($course_id, $scorm_activity_id, $search, $submitted_from, $submitted_to)
    {
        $this->course_id = $course_id;
        $this->scorm_activity_id = $scorm_activity_id;
        $this->submitted_from = $submitted_from;
        $this->submitted_to = $submitted_to;
        $this->search = $search;

        $questions = ScormQuestion::select(['id', 'scorm_activity_id', 'type_of_question', 'question'])->where('scorm_activity_id', $this->scorm_activity_id)->get();
        
        $this->questions = $questions;
    }

    public function collection()
    {
        $searchParts = explode(' ', $this->search);

        $scores = ScormActivityScore::where('course_id', $this->course_id)->where('scorm_activity_id', $this->scorm_activity_id)
            ->whereHas('user', function ($query) use ($searchParts) {
                $query->when($this->search, function($subquery) use ($searchParts) {
                    Helper::queryNameEmailSearch($searchParts, $subquery);
                });
            })
            ->when(($this->submitted_from || $this->submitted_to), function ($query) {
                FilterHelper::queryCreatedAt($query, $this->submitted_from, $this->submitted_to);
            })
            ->with(['user' => function($query) {
                $query->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
            }])->latest()->get();
        
        $scores->each(function($score) {
            $scorm_answers = ScormActivityAnswer::where('scorm_activity_id', $score->scorm_activity_id)->where('user_id', $score->user_id)->get();
            $score->answers = $scorm_answers->groupBy('question_id');
        });

        return collect($scores);
    }

    public function map($data): array
    {
        $answers = [];
        $this->questions->each(function($question) use ($data, &$answers) {
            $answer = $data->answers[$question->id];
            if($question->type_of_question === 'MULTIPLE_CHOICE') {
                array_push($answers, $answer[0]['answer'][0]['title'] ?? '');
            } else if ($question->type_of_question === 'MULTIPLE_RESPONSE') {
                $joined = [];
                foreach ($answer[0]['answer'] as $item) {
                    array_push($joined, $item['title']);
                }
                array_push($answers, implode(', ', $joined) ?? '');
            } else if (in_array($question->type_of_question, ['FILL_IN_THE_BLANK', 'QA_SECTION'])) {
                array_push($answers, $answer[0]['answer']);
            } else if ($question->type_of_question === 'MATCHING'){
                $result = array_map(function($item) {
                    return $item['source']['title'] . ' = ' . $item['target']['title'];
                }, $answer[0]['answer']);
                
                $output = implode("\n", $result);
                array_push($answers, $output);
            } else return array_push($answers, '');
        });

        return [
            $data->user->full_name ?? '',
            $data->user->email ?? '',
            $data->score ?? '',
            ...$answers
        ];
    }

    public function headings(): array
    {

        $questions = $this->questions->map(function($item) {
            return $item['question'];
        });

        return [
            'Name',
            'Email',
            'Score',
            ...$questions
        ];

    }
}
