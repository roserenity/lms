<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\CourseEvaluationQuestion;
use App\Models\Image;
use App\Models\ModuleEvaluationQuestion;
use App\Models\QuestionPool;
use App\Models\QuestionPoolQuestion;
use App\Models\Assessment;
use Illuminate\Http\Request;

class QuestionPoolController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }
    
    public function index() {

        if(request('paginate')) {
            $pools = QuestionPool::whereHas('uploader')->when(request('search'), function($query){
                    $query->where('name', 'LIKE', '%' . request('search') . '%');
                })
                ->when(request('type_of_question_pool'), function($query){
                    $query->where('type_of_question_pool', request('type_of_question_pool'));
                })
                ->with(['uploader' => fn($query) => $query->select(['id', 'first_name', 'last_name', 'suffix', 'email'])])
                ->latest()
                ->paginate(request('paginate'));
        } else {
            $pools = QuestionPool::whereHas('uploader')->when(request('type_of_question_pool'), function($query){
                $query->where('type_of_question_pool', request('type_of_question_pool'));
            })->with(['uploader' => fn($query) => $query->select(['id', 'first_name', 'last_name', 'suffix', 'email'])])
            ->get();
        }
    
        return $this->sendResponse($pools, 'List of all question pools.', 200);
    }

    public function store()
    {

        $question_pool_count = QuestionPool::count();

        $question_pool = QuestionPool::create([
            'name' => 'Question Pool #'. $question_pool_count + 1,
            'type_of_question_pool' => 'assessment',
            'uploader_id' => $this->authUser()->id
        ]);

        return $this->sendResponse($question_pool, 'You have successfully added a new question.', 201);
    }

    public function show($id)
    {
        $question_pool = QuestionPool::where('id', $id)->with(['questions' => function($query){
            $query->with(['answer']);
        }])->first();
        
        if (!$question_pool) {
            return $this->sendResponse([], 'No question pool found.', 404);
        }

        return $this->sendResponse($question_pool, 'Question Pool details', 200);
    }

    public function destroy($id)
    {
        $question_pool = QuestionPool::find($id);
        
        if (!$question_pool) {
            return $this->sendResponse([], 'No question pool found.', 404);
        }

        $question_pool->delete();

        return $this->sendResponse([], 'You have successfully deleted a question pool.', 200);
    }

    public function questionPoolCourseEvaluationAssignment(Request $request)
    {
        $request->validate([
            'question_pool_ids' => 'required|array',
            'question_pool_ids.*' => 'required|exists:question_pools,id',
            'course_evaluation_id' => 'required|exists:course_evaluations,id'
        ]);

        foreach ($request->question_pool_ids as $question_pool_id) {
            $question_pool = QuestionPool::with(['questions' => fn($query) => $query->with('image')])->findOrFail($question_pool_id);

            if ($question_pool->questions->isEmpty()) {
                continue; 
            }

            foreach($question_pool->questions as $question) {
                $course_evaluation = CourseEvaluationQuestion::create([
                    'course_evaluation_id' => $request->course_evaluation_id,
                    'question' => $question->question,
                    'type_of_question' => $question->type_of_question,
                    'is_require_to_answer' => $question->is_require_to_answer,
                    'sort_no' => CourseEvaluationQuestion::where('course_evaluation_id', $request->course_evaluation_id)->count() > 0 ? CourseEvaluationQuestion::where('course_evaluation_id', $request->course_evaluation_id)->max('sort_no') + 1 : 0,
                    'uploader_id' => $this->authUser()->id,
                    'data' => $question->data
                ]);

                foreach($question->image as $image) {
                    $course_evaluation->image()->create([
                        'imageable_id' => $course_evaluation->id,
                        'imageable_type' => 'App\\Models\\CourseEvaluationQuestion',
                        'url' => $image->url
                    ]);
                }
                
            }
        }
        
        return $this->sendResponse([], 'You have successfully imported a question pool to course evaluation', 200);
    }

    
    public function questionPoolModuleEvaluationAssignment(Request $request)
    {
        $request->validate([
            'question_pool_ids' => 'required|array',
            'question_pool_ids.*' => 'required|exists:question_pools,id',
            'module_evaluation_id' => 'required|exists:module_evaluations,id'
        ]);

        foreach ($request->question_pool_ids as $question_pool_id) {
            $question_pool = QuestionPool::with(['questions' => fn($query) => $query->with('image')])->findOrFail($question_pool_id);

            if ($question_pool->questions->isEmpty()) {
                continue; 
            }

            foreach($question_pool->questions as $question) {
                $module_evaluation = ModuleEvaluationQuestion::create([
                    'module_evaluation_id' => $request->module_evaluation_id,
                    'question' => $question->question,
                    'type_of_question' => $question->type_of_question,
                    'is_require_to_answer' => $question->is_require_to_answer,
                    'sort_no' => ModuleEvaluationQuestion::where('module_evaluation_id', $request->module_evaluation_id)->count() > 0 ? ModuleEvaluationQuestion::where('module_evaluation_id', $request->module_evaluation_id)->max('sort_no') + 1 : 0,
                    'uploader_id' => $this->authUser()->id,
                ]);

                foreach($question->answer as $image) {
                    $module_evaluation->image()->create([
                        'imageable_id' => $module_evaluation->id,
                        'imageable_type' => 'App\\Models\\ModuleEvaluationQuestion',
                        'url' => $image->url
                    ]);
                }
                
            }
        }
        
        return $this->sendResponse([], 'You have successfully imported a question pool to module evaluation', 200);
    }

    public function questionPoolAssessmentAssignment(Request $request) {
        
        $request->validate([
            'question_pool_ids' => 'required|array',
            'question_pool_ids.*' => 'required|exists:question_pools,id',
            'assessment_id' => 'required|exists:assessments,id'
        ]);

        $sort_no = AssessmentQuestion::where('assessment_id', $request->assessment_id)->max('sort_no') ?? -1;
        $sort_no = $sort_no + 1;

        foreach ($request->question_pool_ids as $question_pool_id) {
            $question_pool = QuestionPool::with(['questions' => fn($query) => $query->with('answer')])->findOrFail($question_pool_id);

            if ($question_pool->questions->isEmpty()) {
                continue; 
            }

            foreach($question_pool->questions as $question) {

                $assessment_question = AssessmentQuestion::create([
                    'assessment_id' => $request->assessment_id,
                    'question' => $question->question,
                    'score' => $question->score,
                    'type_of_question' => $question->type_of_question,
                    'require_file_attachment' => $question->require_file_attachment,
                    'require_support_answer' => $question->require_support_answer,
                    'uploader_id' => $this->authUser()->id,
                    'rubric_id' => $question->rubric_id,
                    'sort_no' => $sort_no++,
                ]);


                if($question->answer) {
                    $assessment_question->assessment_answer()->create([
                        'assessment_question_id' => $assessment_question->id,
                        'correct_answer' => $question->answer->correct_answer,
                        'choices' => $question->answer->choices,
                    ]);
                }
                
            }
        }
        
        return $this->sendResponse([], 'You have successfully imported a question pool to assessment', 200);

    }
}
