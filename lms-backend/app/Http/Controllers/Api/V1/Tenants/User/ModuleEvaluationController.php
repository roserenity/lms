<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use App\Models\ModuleEvaluation;
use Illuminate\Http\Request;
use App\Models\StudentModuleEvaluationAnswer;
use App\Models\StudentModuleEvaluation;
use App\Models\StudentModuleProgress;
use App\Services\Tenants\ImageService;
use Illuminate\Support\Facades\Log;

class ModuleEvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function show($module_evaluation_id)
    {
        $module_evaluation = ModuleEvaluation::with([
            'module_evaluation_questions' => function ($module_evaluation_questions) {
                return $module_evaluation_questions->with([
                    'image',
                    'answer' => function ($answer) {
                        return $answer->with('image')
                        ->where('user_id', $this->authUser()->id)
                        ->orderBy('id', 'DESC')
                        ->get();
                    }
                ])->orderBy('sort_no', 'ASC');
            }
        ])
            ->find($module_evaluation_id);

        if (!$module_evaluation) {
            return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        return $this->sendResponse($module_evaluation, 'Module evaluation.', 200);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'module_evaluation_id.*' => 'required|exists:module_evaluations,id',
                'module_evaluation_question_id.*' => 'required|exists:module_evaluation_questions,id',
                'answer.*' => 'nullable',
                'file_attachment.*' => 'nullable|sometimes|mimes:jpg,jpeg,png,doc,docx,ppt,pptx,pdf'
            ]);

            $student_module_evaluation = StudentModuleEvaluation::create([
                'user_id' => $this->authUser()->id,
                'module_evaluation_id' => $request->module_evaluation_id[0]
            ]);

            $answers = [];

            foreach ($request->module_evaluation_question_id as $key => $question_id) {
                $student_module_evaluation_answer = StudentModuleEvaluationAnswer::create([
                    'user_id' => $this->authUser()->id,
                    'student_module_evaluation_id' => $student_module_evaluation->id,
                    'module_evaluation_question_id' => $question_id,
                    'data' => $request->answer[$key]
                ]);

                $answers[] = $student_module_evaluation_answer;

                if ($request->hasFile('file_attachment.'.$key)) {
                    ImageService::uploadModuleEvaluationAnswerFileAttachment([
                        'model_id' => $student_module_evaluation_answer->id,
                        'model_type' => 'App\\Models\\StudentModuleEvaluationAnswer',
                        'file_attachment' => $request->file('file_attachment')[$key]
                    ]);
                }
            }

            StudentModuleProgress::updateOrCreate([
                'model_id' => $request->module_evaluation_id[0],
                'model_type' => 'App\\Models\\ModuleEvaluation',
                'course_id' => $request->course_id[0],
                'user_id' => $this->authUser()->id ],
            [
                'status' => StudentModuleProgress::STATUS_DONE,
                'time_spent' => $request->time_spent
            ]);

            return $this->sendResponse([], 'Answer saved.', 201);
        } catch(\Exception $e) {

            if (!empty($answers)) {
                foreach ($answers as $answer) {
                    $answer->delete();
                }
            }

            // Delete the student_course_evaluation
            if (isset($student_module_evaluation)) {
                $student_module_evaluation->delete();
            }
            
            Log::error('Module Evaluation Submission Error: ' . $e);
            return $this->sendResponse([], 'Unexpected response.', 422);
        }
    }

    public function getModuleEvaluation($module_evaluation_id)
    {
        $module_evaluation = ModuleEvaluation::where('id', $module_evaluation_id)->withCount([
            'student_module_evaluations' => function ($student_module_evaluations) {
                return $student_module_evaluations->where('user_id', $this->authUser()->id);
            }, 
            'module_evaluation_questions'
        ])
        ->with([
            'student_module_evaluations' => function ($student_module_evaluations) {
                return $student_module_evaluations->where('user_id', $this->authUser()->id)->orderBy('id', 'DESC');
            }
        ])->first();

        if(!$module_evaluation) {
            return $this->sendResponse([], 'Module evaluation not found.', 404);
        }

        if ($module_evaluation->is_shuffle_questions && $module_evaluation->module_evaluation_questions->isNotEmpty()) {
            $module_evaluation->module_evaluation_questions = $module_evaluation->module_evaluation_questions->shuffle();
        }        

        return $this->sendResponse($module_evaluation, 'Show learner course evaluations', 200);
    }
}
