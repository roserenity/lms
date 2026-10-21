<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\ModuleEvaluationQuestionSortRequest;
use Illuminate\Http\Request;
use App\Http\Requests\Tenants\ModuleEvaluationRequest;
use App\Http\Requests\Tenants\UpdateModuleEvaluationValidityRequest;
use App\Http\Resources\Tenants\Teacher\StudentModuleEvaluationResource;
use App\Models\ModuleEvaluation;
use App\Models\ModuleEvaluationQuestion;
use App\Models\StudentModuleEvaluation;
use App\Models\User;
use App\Models\Module;

class ModuleEvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function show($id)
    {
        // $module_evaluation = ModuleEvaluation::with('module_evaluation_questions')
        //     ->find($id);

        $module_evaluation = ModuleEvaluation::with([
           'module_evaluation_questions' => function ($query) {
             $query->with('image')->orderBy('sort_no', 'ASC');
            }
         ])->find($id);

        if (!$module_evaluation) {
            return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        return $this->sendResponse($module_evaluation, 'Module evaluation.', 200);
    }

    public function store(ModuleEvaluationRequest $request)
    {
        $module_evaluation = ModuleEvaluation::create($request->validated());

        return $this->sendResponse($module_evaluation, 'You have successfully added a new module evaluation.', 201);
    }

    public function update($id, ModuleEvaluationRequest $request)
    {
        $module_evaluation = ModuleEvaluation::find($id);

        if (!$module_evaluation) {
            return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        $module_evaluation->update($request->safe()->except(['module_id', 'uploader_id']));

        return $this->sendResponse($module_evaluation, 'You have successfully updated a module evaluation.', 200);
    }

    public function destroy($id)
    {
        $module_evaluation = ModuleEvaluation::find($id);

        if (!$module_evaluation) {
            return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        $module_evaluation->module_content()->delete(); 
        $module_evaluation->module_evaluation_questions()->delete();
        $module_evaluation->delete();
        //$module_evaluation->student_module_evaluations()->delete();
        //$module_evaluation->student_module_evaluation_answers()->delete();

        return $this->sendResponse([], $module_evaluation->title . ' has been deleted.', 200);
    }

    public function showModuleEvaluationRespondents(Request $request, $module_evaluation_id)
    {
        
        $searchParts = explode(' ', request('search'));

        $module_evaluation = ModuleEvaluation::select(['module_id', 'id'])->find($module_evaluation_id);

        if(!$module_evaluation) {
            return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        $module = Module::select(['id', 'course_id'])->find($module_evaluation->module_id);

        if(!$module) {
            return $this->sendResponse([], 'No module found.', 404);
        }

        $users = User::when(!empty(request('search')), function ($query) use ($searchParts) {
                Helper::queryNameSearch($searchParts, $query);
            })
            ->whereHas('enrollments', function($query) use ($module) {
                $query->where('course_id', $module->course_id);
            })
            ->whereHas('student_module_evaluations', function ($subquery) use ($request, $module_evaluation_id) {
                $subquery->where('module_evaluation_id', $module_evaluation_id)
                    ->when(($request->submitted_from || $request->submitted_to), function ($query) use ($request) {
                        FilterHelper::queryCreatedAt($query, $request->submitted_from, $request->submitted_to);
                    });
            })
            ->with([
                'student_module_evaluations' => function ($student_module_evaluations) use ($module_evaluation_id) {
                    return $student_module_evaluations->with([
                        'answers' => function ($answers) {
                            return $answers->with(['image',
                            'module_evaluation_question' => function($module_evaluation_question) {
                                return $module_evaluation_question->with('image');
                            }]);
                        }, 'user'
                    ])
                        ->where('module_evaluation_id', $module_evaluation_id)
                        ->orderBy('id', 'DESC')
                        ->get();
                }
            ])
            ->paginate(request('paginate'));

        $usersResource = StudentModuleEvaluationResource::collection($users);

        $users->data = $usersResource;

        return $this->sendResponse($users, 'List of module evaluation respondents.', 200);
    }

    public function showModuleEvaluationRespondentAttempts($module_evaluation_id, $user_id)
    {
        $user = User::with([
            'student_module_evaluations' => function ($student_module_evaluations) use ($module_evaluation_id) {
                return $student_module_evaluations->where('module_evaluation_id', $module_evaluation_id);
            }
        ])
            ->where('id', $user_id)
            ->first();

        return $this->sendResponse($user, 'List of module evaluation respondent attempts.', 200);
    }

    public function showModuleEvaluationRespondentAnswers($student_module_evaluation_id, $user_id)
    {
        $user = User::with([
            'student_module_evaluations' => function ($student_module_evaluations) use ($student_module_evaluation_id) {
                return $student_module_evaluations->with([
                    'answers' => function ($answers) {
                        $answers->with(['image']);
                    }
                ])
                    ->where('id', $student_module_evaluation_id)
                    ->has('answers');
            }
        ])
            ->where('id', $user_id)
            ->first();

        return $this->sendResponse($user, 'List of module evaluation respondents.', 200);
    }

    public function updateStudentModuleEvaluationStatus($student_module_evaluation_id)
    {
        $student_module_evaluation = StudentModuleEvaluation::find($student_module_evaluation_id);

        if (!$student_module_evaluation) {
            return $this->sendResponse([], 'Student module evaluation not found', 404);
        }

        $student_module_evaluation->update(['is_checked' => true]);

        return $this->sendResponse($student_module_evaluation, 'Student module evaluation checked.', 200);
    }

    public function moduleEvaluationAnswers(Request $request, $module_evaluation_id)
    {

        $studentModuleEvaluations = StudentModuleEvaluation::where('module_evaluation_id', $module_evaluation_id)
            ->when(($request->submitted_from || $request->submitted_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->submitted_from, $request->submitted_to);
            })
            ->with(['answers' => function($answers) {
                return $answers->with('user');
            }])
            ->latest()
            ->distinct("user_id")
            ->groupBy('user_id')
            ->get();

        $answers = collect($studentModuleEvaluations->pluck('answers')->flatten())->groupBy('module_evaluation_question_id');

        return $this->sendResponse($answers, 'List of module evaluation respondents answer summary', 200);
    }

    public function updateModuleEvaluationValidity(UpdateModuleEvaluationValidityRequest $request, $module_evaluation_id)
    {
        request()->validate([
            'date' => 'in:anytime,' . date('Y-m-d', strtotime(request('date')))
        ]);

        $module_evaluation = ModuleEvaluation::find($module_evaluation_id);

        if (!$module_evaluation) {
        return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        $module_evaluation->update([
            'validity' => $request->validity,
            'available_from' => $request->available_from,
            'available_to' => $request->available_to,
        ]);

        return $this->sendResponse($module_evaluation, 'Course module validity has been updated.', 200);
    }

    public function sortQuestions(ModuleEvaluationQuestionSortRequest $request){
        
        foreach ($request->module_evaluation_question_id as $key => $module_evaluation_question_id) {
            ModuleEvaluationQuestion::where('id', $module_evaluation_question_id)->update(['sort_no' => $key]);
        }
    
        $module_evaluation_questions = ModuleEvaluationQuestion::where('module_evaluation_id', $request->module_evaluation_id)->orderBy('sort_no', 'ASC')->get();
    
        return $this->sendResponse($module_evaluation_questions, 'You have successfully sort module evaluation questions.', 200);
        
    }
}
