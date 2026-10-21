<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Tenants\ModuleEvaluationQuestionRequest;
use App\Models\Module;
use App\Models\ModuleEvaluation;
use App\Models\ModuleEvaluationQuestion;
use App\Models\StudentModuleEvaluation;
use App\Services\Tenants\ImageService;
use App\Models\Image;

use App\Helpers\FilterHelper;

class ModuleEvaluationQuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function store(ModuleEvaluationQuestionRequest $request)
    {
        $module_evaluation_question = ModuleEvaluationQuestion::create($request->validated());

        return $this->sendResponse($module_evaluation_question, 'You have successfully added a new question.', 201);
    }

    public function destroy($id)
    {
        $module_evaluation_question = ModuleEvaluationQuestion::find($id);

        if (!$module_evaluation_question) {
            return $this->sendResponse([], 'No module evaluation question found.', 404);
        }

        $module_evaluation_question->delete();

        return $this->sendResponse([], $module_evaluation_question->title . ' has been deleted.', 200);
    }

    public function getQuestions($id){

        $module_evaluation = ModuleEvaluation::with([
           'module_evaluation_questions' => function ($query) {
             $query->with('image');
             }
         ])->find($id);

        if (!$module_evaluation) {
            return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        return $this->sendResponse($module_evaluation, 'Module evaluation questions.', 200);

    }

    public function moduleEvaluationQuestionAnswers(Request $request, $module_evaluation_id, $module_evaluation_question_id)
    {
        $module_evaluation = ModuleEvaluation::select(['module_id', 'id'])->find($module_evaluation_id);

        if(!$module_evaluation) {
            return $this->sendResponse([], 'No module evaluation found.', 404);
        }

        $module = Module::select(['id', 'course_id'])->find($module_evaluation->module_id);

        if(!$module) {
            return $this->sendResponse([], 'No module found.', 404);
        }

        $studentModuleEvaluations = StudentModuleEvaluation::where('module_evaluation_id', $module_evaluation_id)
            ->when(($request->submitted_from || $request->submitted_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->submitted_from, $request->submitted_to);
            })
            ->whereHas('user', function($query) use ($module) {
                FilterHelper::filterEnrolledUsers($query, $module->course_id);
            })
            ->with(['answers' => function($answers) use ($module_evaluation_question_id) {
                return $answers->where('module_evaluation_question_id', $module_evaluation_question_id)->with([
                    'user' => fn($user) => $user->select(['id', 'first_name', 'last_name', 'suffix'])
                ])->latest()->get();
            }])
            ->latest()
            ->distinct("user_id")
            ->get();

        $answers = $studentModuleEvaluations->pluck('answers');


        return $this->sendResponse($answers, 'List of module evaluation respondents answer summary for question', 200);
    }

    public function updateQuestions(ModuleEvaluationQuestionRequest $request)
    {
        foreach ($request->module_evaluation_question_id as $key => $question_id) {
            ModuleEvaluationQuestion::find($question_id)->update([
                'question' => $request->question[$key]?? NULL,
                'type_of_question' => $request->type_of_question[$key] ?? NULL,
                'is_require_to_answer' => $request->is_require_to_answer[$key] ?? NULL,
                'data' => $request->data[$key] ?? NULL,
            ]);
        }

        return $this->sendResponse([], 'Questions updated.', 200);
    }

    public function uploadQuestionImage(Request $request, $module_evaluation_question_id)
    {
        $request->validate([
            'image' => 'nullable|mimes:png,jpg,jpeg'
        ]);

        $module_evaluation_question = ModuleEvaluationQuestion::find($module_evaluation_question_id);

        if (!$module_evaluation_question) {
            return $this->sendResponse([], 'No course evaluation question found.', 404);
        }

        if ($request->hasFile('image')) {
            ImageService::uploadModuleEvaluationQuestionImage([
                'model_id' => $module_evaluation_question_id,
                'model_type' => 'App\\Models\\ModuleEvaluationQuestion',
                'image' => $request->file('image')
            ]);
        }
    }

    public function deleteQuestionImage($id)
    {
        Image::find($id)->delete();

        return $this->sendResponse([], 'Question image deleted.', 200);
    }

    public function getQuestionImages($module_evaluation_question_id)
    {
        $module_evaluation_question = ModuleEvaluationQuestion::where('id', $module_evaluation_question_id);
    
        if (! $module_evaluation_question) {
          return $this->sendResponse([], 'No evaluation question found.', 404);
        }
    
        return $this->sendResponse($module_evaluation_question->with('image')->get(), 'Question images.', 200);
    }
}
