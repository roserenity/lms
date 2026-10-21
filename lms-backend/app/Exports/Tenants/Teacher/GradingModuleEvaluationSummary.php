<?php

namespace App\Exports\Tenants\Teacher;

use App\Helpers\FilterHelper;

use App\Models\Exports;
use App\Models\ModuleEvaluation;
use App\Models\StudentModuleEvaluation;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

use Log;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class GradingModuleEvaluationSummary implements FromQuery, WithMapping, WithHeadings, ShouldQueue
{
    use Exportable;

    private $fileName = 'module_evaluation_summary.csv';

    private $writerType = Excel::CSV;

    private $headers = [
        'Content-Type' => 'text/csv',
    ];

    protected $export_record_id;
    protected $module_evaluation_id;
    protected $submitted_from;
    protected $submitted_to;
    protected $questions;
    protected $course_id;

    public function __construct($export_record_id, $module_evaluation_id, $submitted_from, $submitted_to, $course_id)
    {
        $this->export_record_id = $export_record_id;
        $this->module_evaluation_id = $module_evaluation_id;
        $this->submitted_from = $submitted_from;
        $this->submitted_to = $submitted_to;
        $this->questions = [];
        $this->course_id = $course_id;

        $this->loadIds();
    }

    private function loadIds()
    {
        $module_evaluation = ModuleEvaluation::with('module_evaluation_questions')->find($this->module_evaluation_id);

        if ($module_evaluation) {
            $questions = [];
            foreach ($module_evaluation->module_evaluation_questions as $question) {
                if($question->question) {
                    $questions[] = $question;
                }
            }
            $this->questions = $questions;
        }

    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [$this, 'afterWriting']
        ];
    }
    
    public function afterWriting(AfterSheet $event) 
    {

        $exportRecord = Exports::find($this->export_record_id);
        
        $exportRecord->update([
            'status' => 'completed',
        ]);

        $exportRecord->save();
    }

    
    public function chunkSize(): int
    {
        return 1000; // adjust as needed
    }

    public function query()
    {
        return StudentModuleEvaluation::query()->where('module_evaluation_id', $this->module_evaluation_id)
            ->when(($this->submitted_from || $this->submitted_to), function ($query) {
                FilterHelper::queryCreatedAt($query, $this->submitted_from, $this->submitted_to);
            })
            ->whereHas('user', function($query) {
                FilterHelper::filterEnrolledUsers($query, $this->course_id);
            })
            ->latest()
            ->distinct('user_id')
            ->with('user')
            ->with(['answers' => function($answers) {
                return $answers;
            }]);
    }

    public function collection()
    {
        $student_module_evaluations = StudentModuleEvaluation::where('module_evaluation_id', $this->module_evaluation_id)
            ->when(($this->submitted_from || $this->submitted_to), function ($query) {
                FilterHelper::queryCreatedAt($query, $this->submitted_from, $this->submitted_to);
            })
            ->whereHas('user', function($query) {
                FilterHelper::filterEnrolledUsers($query, $this->course_id);
            })
            ->with(['answers' => function($answers) {
                return $answers->with('user');
            }])->get();

        $student_module_evaluations = $student_module_evaluations->groupBy('user_id')->map(function ($group) {
            return $group->sortByDesc('created_at')->first();
        });

        $users = collect($student_module_evaluations->pluck('answers')->flatten())->groupBy('user_id')->map(function ($userAnswers) {
            return $userAnswers->groupBy('module_evaluation_question_id');
        });

        $answer = [];

        foreach($users as $user) {
            $user_answer = [];
            if($user->first()->first()) {
                $obj = $user->first()->first();
                $student_module_eval = $obj->student_module_evaluation_id;
                $name = $obj->user->first_name.' '. $obj->user->last_name . ' '. $obj->user->suffix;
                $email = $obj->user->email;
                $answers = [];

                foreach ($this->ids as $id) {
                    if($user->get($id)) {
                        $data = $user->get($id);
                        $question = $data->first()->module_evaluation_question;

                        if($question->id == $id) {
                            if($question) {
                                if($question->type_of_question === 'multiple_choice_grid') {
                                    if(is_array(json_decode($data->first()->data)->answer) || is_object(json_decode($data->first()->data)->answer)) {
                                        if(json_decode($data->first()->data)->answer !== 'choice_0') {
                                            $user_answer_data = get_object_vars(json_decode($data->first()->data)->answer);
                                            foreach ($question->data as $rowKey => $rowValue) {
                                                if (strpos($rowKey, 'row_') === 0) {
                                                    $found = false;
                                                    foreach ($question->data as $colKey => $colValue) {
                                                        if (strpos($colKey, 'col_') === 0) {
                                                            if(in_array($rowKey.'_'.$colKey, $user_answer_data)) {
                                                                $found = true;
                                                                array_push($answers, $colValue);
                                                            }
                                                        }
                                                    }
                                                    if(!$found) {
                                                        array_push($answers, ' ');
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        foreach ($question->data as $colKey => $colValue) {
                                            if (strpos($colKey, 'row_') === 0) {
                                                array_push($answers, ' ');
                                            }
                                        }
                                    }
                                } else if ($question->type_of_question === 'checkbox_grid') {
                                    if(json_decode($data->first()->data)) {
                                        $user_answer_data = json_decode($data->first()->data)->answer;
                                        foreach ($question->data as $rowKey => $rowValue) {
                                            if (strpos($rowKey, 'row_') === 0) {
                                                $found = false;
                                                $checkbox_answers = [];
                                                foreach ($question->data as $colKey => $colValue) {
                                                    if (strpos($colKey, 'col_') === 0) {
                                                        if(in_array($rowKey.'_'.$colKey, $user_answer_data)) {
                                                            $found = true;
                                                            array_push($checkbox_answers, $colValue);  
                                                        }
                                                    }
                                                }
                                                if(!$found) {
                                                    array_push($answers, ' ');
                                                } else {
                                                    array_push($answers, join(',', $checkbox_answers));
                                                }
                                            }
                                        }
                                        
                                    } else {
                                        array_push($answers, ' ');
                                    }
                                } else if ( $question->type_of_question === 'linear_scale' ) {
                                    if(json_decode($data->first()->data)) {
                                        array_push($answers, str_replace('scale_', '', json_decode($data->first()->data)->answer));
                                    } else {
                                        array_push($answers, ' ');
                                    }
                                } else if ( $question->type_of_question === 'dropdown' ) {
                                    if(json_decode($data->first()->data)) {
                                        $user_answer_data = json_decode($data->first()->data)->answer;
                                        $matchFound = false;
                                        foreach ($question->data as $rowKey => $rowValue) {
                                            if ($user_answer_data === $rowKey) {
                                                array_push($answers, $rowValue);
                                                $matchFound = true;
                                                break;
                                            }
                                        }
                                        if (!$matchFound) {
                                            array_push($answers, ' ');
                                        }
                                    } else {
                                        array_push($answers, ' ');
                                    }
                                } else if ( $question->type_of_question === 'checkbox' ) {
                                    if(json_decode($data->first()->data)) {
                                        $user_answer_data = json_decode($data->first()->data)->answer;
                                        $data = [];
    
                                        foreach ($question->data as $rowKey => $rowValue) {
    
                                            if(is_array($user_answer_data)) {
                                                array_push($data, $rowValue);
                                            } else if($rowKey === $user_answer_data) {
                                                array_push($data, $rowValue);
                                            }
                                        }
                                        array_push($answers, join(',', $data));
                                    } else {
                                        array_push($answers, ' ');
                                    }
                                    
                                } else {
                                    if(json_decode($data->first()->data)) {
                                        if ($question->type_of_question == 'multiple_choice') {
                                            $user_answer_data = json_decode($data->first()->data)->answer;
                                            if(is_array(json_decode($data->first()->data)->answer) || is_object(json_decode($data->first()->data)->answer)) {
                                                foreach ($question->data as $rowKey => $rowValue) {
                                                    if ($rowKey === $user_answer_data) { 
                                                        array_push($answers, $rowValue);
                                                    }
                                                }
                                            } else {
                                                if(json_decode($data->first()->data)) {
                                                    $user_answer_data = json_decode($data->first()->data)->answer;
                                                    $matchFound = false;
                                                    foreach ($question->data as $rowKey => $rowValue) {
                                                        if ($user_answer_data === $rowKey) {
                                                            array_push($answers, $rowValue);
                                                            $matchFound = true;
                                                            break;
                                                        }
                                                    }
                                                    if (!$matchFound) {
                                                        array_push($answers, ' ');
                                                    }
                                                } else {
                                                    array_push($answers, ' ');
                                                }
                                            }
                                        } else if ($question->type_of_question == 'paragraph') {
                                            if(is_array(json_decode($data->first()->data)->answer) || is_object(json_decode($data->first()->data)->answer)) {
                                                array_push($answers, ' ');
                                            } else {
                                                array_push($answers, json_decode($data->first()->data)->answer);
                                            }
                                        } else {
                                            $user_answer_data = json_decode($data->first()->data)->answer;
                                            if (is_scalar($user_answer_data)) {
                                                array_push($answers, $user_answer_data);
                                            } elseif (is_array($user_answer_data) || is_object($user_answer_data)) {
                                                array_push($answers, json_encode($user_answer_data));
                                            } else {
                                                array_push($answers, ' ');
                                            }
                                        }

                                    } else {
                                        array_push($answers, ' ');
                                    }
                                }
                            } else {
                                array_push($answers, ' ');
                            }
                        }
                    } else {
                        array_push($answers, '');
                    }
                    
                }
            
                array_push($user_answer, $name);
                array_push($user_answer, $email);
                array_push($user_answer, ...$answers);

                $student_module_eval = StudentModuleEvaluation::find($student_module_eval);

                if($student_module_eval) {
                    array_push($user_answer, Carbon::parse($student_module_eval->created_at)->toDateString());
                    array_push($user_answer, Carbon::parse($student_module_eval->created_at)->toTimeString());
                } else {
                    array_push($user_answer, '');
                    array_push($user_answer, '');
                }

                array_push($answer, $user_answer);
            }
        }

        return collect($answer);
    }

    public function map($data): array
    {
        
        $answers = [];

        $data_collection = collect($data);

        Log::info($data_collection);

        if (!$data_collection->has('user')) {
            return [];
        }

        foreach($this->questions as $question) {


            if(empty($data_collection['answers']) || !isset($data_collection['answers'])) {
                array_push($answers, ' ');
            } else {
                $answer = array_filter($data_collection['answers'], function($answer) use ($question) {
                    return $answer['module_evaluation_question_id'] == $question->id;
                });

                $answer = collect($answer)->first();

                if(!$answer || empty($answer)) {
                    array_push($answers, ' ');
                } else if($question) {
                    if($question->type_of_question === 'multiple_choice_grid') {
                        if(is_string(json_decode($answer['data']))){
                            array_push($answers, ' ');
                        } else if(is_array(json_decode($answer['data'])->answer) || is_object(json_decode($answer['data'])->answer)) {
                            if(json_decode($answer['data'])->answer !== 'choice_0') {
                                $user_answer_data = get_object_vars(json_decode($answer['data'])->answer);
                                foreach ($question->data as $rowKey => $rowValue) {
                                    if (strpos($rowKey, 'row_') === 0) {
                                        $found = false;
                                        foreach ($question->data as $colKey => $colValue) {
                                            if (strpos($colKey, 'col_') === 0) {
                                                if(in_array($rowKey.'_'.$colKey, $user_answer_data)) {
                                                    $found = true;
                                                    array_push($answers, $colValue);
                                                }
                                            }
                                        }
                                        if(!$found) {
                                            array_push($answers, ' ');
                                        }
                                    }
                                }
                            }
                        } else {
                            foreach ($question->data as $colKey => $colValue) {
                                if (strpos($colKey, 'row_') === 0) {
                                    array_push($answers, ' ');
                                }
                            }
                        }
                    } else if ($question->type_of_question === 'checkbox_grid') {
                        if(json_decode($answer['data'])) {
                            $user_answer_data = json_decode($answer['data'])->answer;
                            foreach ($question->data as $rowKey => $rowValue) {
                                if (strpos($rowKey, 'row_') === 0) {
                                    $found = false;
                                    $checkbox_answers = [];
                                    foreach ($question->data as $colKey => $colValue) {
                                        if (strpos($colKey, 'col_') === 0) {
                                            if(in_array($rowKey.'_'.$colKey, $user_answer_data)) {
                                                $found = true;
                                                array_push($checkbox_answers, $colValue);  
                                            }
                                        }
                                    }
                                    if(!$found) {
                                        array_push($answers, ' ');
                                    } else {
                                        array_push($answers, join(',', $checkbox_answers));
                                    }
                                }
                            }
                            
                        } else {
                            array_push($answers, ' ');
                        }
                    } else if ( $question->type_of_question === 'linear_scale' ) {
                        if(json_decode($answer['data'])) {
                            array_push($answers, str_replace('scale_', '', json_decode($answer['data'])->answer));
                        } else {
                            array_push($answers, ' ');
                        }
                    } else if ( $question->type_of_question === 'dropdown' ) {
                        if(json_decode($answer['data'])) {
                            $user_answer_data = json_decode($answer['data'])->answer;
                            $matchFound = false;
                            foreach ($question->data as $rowKey => $rowValue) {
                                if ($user_answer_data === $rowKey) {
                                    array_push($answers, $rowValue);
                                    $matchFound = true;
                                    break;
                                }
                            }
                            if (!$matchFound) {
                                array_push($answers, ' ');
                            }
                        } else {
                            array_push($answers, ' ');
                        }
                    } else if ( $question->type_of_question === 'checkbox' ) {
                        if(json_decode($answer['data'])) {
                            $user_answer_data = json_decode($answer['data'])->answer;
                            $data = [];

                            foreach ($question->data as $rowKey => $rowValue) {

                                if(is_array($user_answer_data)) {
                                    array_push($data, $rowValue);
                                } else if($rowKey === $user_answer_data) {
                                    array_push($data, $rowValue);
                                }
                            }
                            array_push($answers, join(',', $data));
                        } else {
                            array_push($answers, ' ');
                        }
                        
                    } else {
                        if(json_decode($answer['data'])) {
                            if ($question->type_of_question == 'multiple_choice') {
                                $user_answer_data = json_decode($answer['data'])->answer;
                                if(is_array(json_decode($answer['data'])->answer) || is_object(json_decode($answer['data'])->answer)) {
                                    foreach ($question->data as $rowKey => $rowValue) {
                                        if ($rowKey === $user_answer_data) { 
                                            array_push($answers, $rowValue);
                                        }
                                    }
                                } else {
                                    if(json_decode($answer['data'])) {
                                        $user_answer_data = json_decode($answer['data'])->answer;
                                        $matchFound = false;
                                        foreach ($question->data as $rowKey => $rowValue) {
                                            if ($user_answer_data === $rowKey) {
                                                array_push($answers, $rowValue);
                                                $matchFound = true;
                                                break;
                                            }
                                        }
                                        if (!$matchFound) {
                                            array_push($answers, ' ');
                                        }
                                    } else {
                                        array_push($answers, ' ');
                                    }
                                }
                            } else if ($question->type_of_question == 'paragraph') {
                                if(is_array(json_decode($answer['data'])->answer) || is_object(json_decode($answer['data'])->answer)) {
                                    array_push($answers, ' ');
                                } else {
                                    array_push($answers, json_decode($answer['data'])->answer);
                                }
                            } else {
                                $user_answer_data = json_decode($answer['data'])->answer;
                                if (is_scalar($user_answer_data)) {
                                    array_push($answers, $user_answer_data);
                                } elseif (is_array($user_answer_data) || is_object($user_answer_data)) {
                                    array_push($answers, json_encode($user_answer_data));
                                } else {
                                    array_push($answers, ' ');
                                }
                            }

                        } else {
                            array_push($answers, ' ');
                        }
                    }
                } else {
                    array_push($answers, ' ');
                }
            }
        }

        if($data_collection->get('user')['full_name']) {
            return [
                $data_collection->get('user')['full_name'] ?? null,
                $data_collection->get('user')['email'] ?? null,
                ...$answers,
                Carbon::parse($data_collection->get('user')['created_at'])->toDateString(),
                Carbon::parse($data_collection->get('user')['created_at'])->toTimeString()
            ];
        } else return [];
    }

    public function headings(): array
    {
        $module_evaluation = ModuleEvaluation::with(['module_evaluation_questions'])->where('id', $this->module_evaluation_id)->first();

        $questions = [];

        if ($module_evaluation) {
            foreach($module_evaluation->module_evaluation_questions as $question) {
                if($question->type_of_question === 'multiple_choice_grid' || $question->type_of_question === 'checkbox_grid') {
                    foreach ($question->data as $key => $value) {
                        if (strpos($key, 'row_') === 0) {
                            array_push($questions, $question->question.' - '. $value);
                        }
                    }
                } else {
                    array_push($questions, $question->question);
                }
            
            }   
        }

        return ['Name', 'Email', ...$questions, 'Completion Date', 'Completion Time'];
    }
}
