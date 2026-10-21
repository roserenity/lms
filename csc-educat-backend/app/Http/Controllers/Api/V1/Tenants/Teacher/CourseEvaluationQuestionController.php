<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Tenants\CourseEvaluationQuestionRequest;
use App\Models\CourseEvaluation;
use App\Models\CourseEvaluationQuestion;
use App\Models\StudentCourseEvaluation;
use App\Services\Tenants\ImageService;
use App\Models\Image;
use App\Helpers\FilterHelper;

class CourseEvaluationQuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function store(CourseEvaluationQuestionRequest $request)
    {
        $course_evaluation_question = CourseEvaluationQuestion::create($request->validated());

        return $this->sendResponse($course_evaluation_question, 'You have successfully added a new question.', 201);
    }

    public function destroy($id)
    {
        $course_evaluation_question = CourseEvaluationQuestion::find($id);

        if (!$course_evaluation_question) {
            return $this->sendResponse([], 'No course evaluation question found.', 404);
        }

        $course_evaluation_question->delete();

        return $this->sendResponse([], $course_evaluation_question->title . ' has been deleted.', 200);
    }

    public function getQuestions($id){

        $course_evaluation = CourseEvaluation::with([
           'course_evaluation_questions' => function ($query) {
             $query->with('image');
             }
         ])->find($id);

        if (!$course_evaluation) {
            return $this->sendResponse([], 'No course evaluation found.', 404);
        }

        return $this->sendResponse($course_evaluation, 'Course evaluation questions.', 200);
    }

    public function courseEvaluationQuestionAnswers(Request $request, $course_evaluation_id, $course_evaluation_question_id)
    {

        $course_evaluation = CourseEvaluation::select(['id', 'course_id'])->find($request->course_evaluation_id);
  
        if (!$course_evaluation) {
            return $this->sendResponse([], 'No course evaluation found.', 404);
        }

        $student_course_evaluations = StudentCourseEvaluation::where('course_evaluation_id', $course_evaluation_id)
            ->when(($request->submitted_from || $request->submitted_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->submitted_from, $request->submitted_to);
            })
            ->whereHas('user', function($subQuery) use ($course_evaluation) {
                FilterHelper::filterEnrolledUsers($subQuery, $course_evaluation->course_id);
            })
            ->with(['answers' => function($answers) use ($course_evaluation_question_id) {
                return $answers->where('course_evaluation_question_id', $course_evaluation_question_id)->with([
                    'user' => fn($user) => $user->select(['id', 'first_name', 'last_name', 'suffix'])
                ])->latest()->get();
            }])
            ->latest()
            ->distinct("user_id")
            ->get();

        $answers = $student_course_evaluations->pluck('answers');


        return $this->sendResponse($answers, 'List of course evaluation respondents answer summary for question', 200);
    }

    public function updateQuestions(CourseEvaluationQuestionRequest $request)
    {
        foreach ($request->course_evaluation_question_id as $key => $question_id) {
            CourseEvaluationQuestion::find($question_id)->update([
                'question' => $request->question[$key]?? NULL,
                'type_of_question' => $request->type_of_question[$key] ?? NULL,
                'is_require_to_answer' => $request->is_require_to_answer[$key] ?? NULL,
                'data' => $request->data[$key] ?? NULL,
            ]);
        }

        return $this->sendResponse([], 'Questions updated.', 200);
    }

    public function uploadQuestionImage(Request $request, $course_evaluation_question_id)
    {
        $request->validate([
            'image' => 'nullable|mimes:png,jpg,jpeg'
        ]);

        $course_evaluation_question = CourseEvaluationQuestion::find($course_evaluation_question_id);

        if (!$course_evaluation_question) {
            return $this->sendResponse([], 'No course evaluation question found.', 404);
        }

        if ($request->hasFile('image')) {
            ImageService::uploadCourseEvaluationQuestionImage([
                'model_id' => $course_evaluation_question_id,
                'model_type' => 'App\\Models\\CourseEvaluationQuestion',
                'image' => $request->file('image')
            ]);
        }
    }

    public function getQuestionImages($course_evaluation_question_id)
    {
        $course_evaluation_question = CourseEvaluationQuestion::where('id', $course_evaluation_question_id);

        if (! $course_evaluation_question) {
          return $this->sendResponse([], 'No evaluation question found.', 404);
        }

        return $this->sendResponse($course_evaluation_question->with('image')->get(), 'Question images.', 200);
    }

    public function deleteQuestionImage($id)
    {
        Image::find($id)->delete();

        return $this->sendResponse([], 'Question image deleted.', 200);
    }
}
