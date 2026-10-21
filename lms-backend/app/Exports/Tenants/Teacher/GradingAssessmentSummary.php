<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\FilterHelper;

use App\Models\User;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;

use Illuminate\Support\Facades\Log;

class GradingAssessmentSummary implements FromCollection, Responsable, WithMapping, WithHeadings
{
    use Exportable;

    private $fileName = 'assessment_summary.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $assessment_id;
    protected $question_ids;
    protected $submitted_from;
    protected $submitted_to;

    public function __construct($assessment_id, $submitted_from, $submitted_to)
    {
        $this->assessment_id = $assessment_id;
        $this->submitted_from = $submitted_from;
        $this->submitted_to = $submitted_to;
        $this->question_ids = [];
    }

    public function collection()
    {
        $answers = AssessmentScore::query()->where('assessment_id', $this->assessment_id)
            ->when(($this->submitted_from || $this->submitted_to), function ($query) {
                FilterHelper::queryCreatedAt($query, $this->submitted_from, $this->submitted_to);
            })
            ->where('active', true)
            ->with('user')
            ->get();

        $answer = [];

        foreach ($answers as $data) {
            if($data->all_question_answers_with_questions()) {
                $data->all_question_answers = $data->all_question_answers_with_questions();
                $user_answers = [];
    
                array_push($user_answers, $data->user->first_name.' '. $data->user->last_name . ' '. $data->user->suffix);
    
                foreach($this->question_ids as $id)
                {
                    $user_ans = $data->all_question_answers->where('assessment_question_id', $id)->first();
                    
                    if($user_ans) {
                        $question = $user_ans->assessment_question;
                        $type_of_question = $user_ans->assessment_question->type_of_question;
                        $choices = $user_ans->assessment_question->assessment_answer->choices;
                        $correct_answer = $user_ans->assessment_question->assessment_answer->correct_answer;
                        $user_answer = $user_ans->answer;
    
                        //setting of learner answer
                        if($user_answer) {
    
                            if($type_of_question == 'identification') {
                                array_push($user_answers, strip_tags($user_answer));
                            } else if ($type_of_question == 'ordering') {
                                $choices_temp = array_reduce($choices, function ($choice, $item) {
                                    return $choice + $item;
                                }, []);
    
                                $answers_temp = explode(",", $user_answer);
    
                                $_temp = [];
                                foreach ($answers_temp as $answer_temp) {
                                    if (array_key_exists($answer_temp, $choices_temp)){
                                        array_push($_temp, $choices_temp[$answer_temp]);
                                    }
                                }
                                array_push($user_answers, implode(PHP_EOL , $_temp));
                            } else if ($type_of_question == 'essay') {
                                array_push($user_answers, strip_tags($user_answer));
                            } else if ($type_of_question == 'sliders') {
                                $_temp = ' ';
                                foreach ($choices as $choice) {
                                    if (array_key_exists('choice_'.$user_answer, $choice)){
                                        $_temp = $choice['choice_'.$user_answer];
                                    }
                                }
                                array_push($user_answers, $_temp);
                            } else {
                                $_temp = ' ';
                                foreach ($choices as $choice) {
                                    if (array_key_exists($user_answer, $choice)){
                                        $_temp = $choice[$user_answer];
                                    }
                                }
                                array_push($user_answers, $_temp);
                            }
                        } else {
                            array_push($user_answers, " ");
                        }
    
                        //setting of correct answer
                        if($type_of_question != 'essay' && $type_of_question != 'sliders') {
                            if($type_of_question == 'identification') {
                                $correct_answer = implode(', ', array_map(fn($choice) => current($choice), $choices));
                                array_push($user_answers, $correct_answer);
                            } else if($type_of_question == 'ordering') {
                                $correct_answer = implode(PHP_EOL , array_map(fn($choice) => current($choice), $choices));
                                array_push($user_answers, $correct_answer);
                            } else if (!$choices || !$correct_answer ){
                                array_push($user_answers, " ");
                            } else if($correct_answer) {
                                $_temp = ' ';
                                foreach ($choices as $choice) {
                                    if (array_key_exists($correct_answer, $choice)){
                                        $_temp = $choice[$correct_answer];
                                    }
                                }
                                array_push($user_answers, $_temp);
                            }
                        }
    
                        if($question->require_support_answer && ($type_of_question == 'justified_multiple_choice' || $type_of_question == 'modified_true_or_false'))
                        {
                            array_push($user_answers, $user_ans->support_answer);
                        }
    
                        if($question->require_file_attachment)
                        {
                            array_push($user_answers, $user_ans->file);
                        }
                        
                    }
                }
    
                array_push($answer, $user_answers);
            }
        }
            

        return collect($answer);
    }

    public function map($data): array
    {
        return [...$data];
    }

    public function headings(): array
    {
        
        $assessment = Assessment::query()->where('id', $this->assessment_id)->with('assessment_questions')->first();

        $headers = [];

        foreach($assessment->assessment_questions as $question)
        {
            array_push($this->question_ids, strip_tags($question->id));
            array_push($headers, strip_tags($question->question).' - Answer');

            if($question->type_of_question != 'essay' && $question->type_of_question != 'sliders')
            {
                array_push($headers, strip_tags($question->question).' - Correct Answer');
            }

            if($question->require_support_answer && $question->type_of_question == 'justified_multiple_choice')
            {
                array_push($headers, strip_tags($question->question).' - Justification');
            }

            if($question->require_support_answer && $question->type_of_question == 'modified_true_or_false')
            {
                array_push($headers, strip_tags($question->question).' - Modified Question');
            }

            if($question->require_file_attachment)
            {
                array_push($headers, strip_tags($question->question).'- File Attachment');
            }
        }

        return ['Name', ...$headers];
    }
}
