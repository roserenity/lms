<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\CourseEvaluationQuestionSortRequest;
use App\Models\CourseEvaluation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Tenants\CourseEvaluationRequest;
use App\Http\Requests\Tenants\UpdateCourseEvaluationValidityRequest;
use App\Http\Resources\Tenants\Teacher\StudentCourseEvaluationResource;
use App\Models\CourseEvaluationQuestion;
use App\Models\StudentCourseEvaluation;
use Illuminate\Support\Facades\Log;

class CourseEvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function show($id)
    {
        // $course_evaluation = CourseEvaluation::with('course_evaluation_questions')
        //     ->find($id);

        $course_evaluation = CourseEvaluation::with([
            'course_evaluation_questions' => function ($query) {
                $query->with('image')->orderBy('sort_no', 'ASC');
            }
        ])->find($id);

        if (!$course_evaluation) {
            return $this->sendResponse([], 'No course evaluation found.', 404);
        }

        return $this->sendResponse($course_evaluation, 'Course evaluation.', 200);
    }

    public function store(CourseEvaluationRequest $request)
    {
        $course_evaluation = CourseEvaluation::create($request->validated());

        return $this->sendResponse($course_evaluation, 'You have successfully added a new course evaluation.', 201);
    }

    public function update($id, CourseEvaluationRequest $request)
    {
        $course_evaluation = CourseEvaluation::find($id);

        if (!$course_evaluation) {
            return $this->sendResponse([], 'No course evaluation found.', 404);
        }

        $course_evaluation->update($request->safe()->except(['course_id', 'uploader_id']));

        return $this->sendResponse($course_evaluation, 'You have successfully updated a course evaluation.', 200);
    }

    public function destroy($id)
    {
        $course_evaluation = CourseEvaluation::find($id);

        if (!$course_evaluation) {
            return $this->sendResponse([], 'No course evaluation found.', 404);
        }

        $course_evaluation->delete();
        $course_evaluation->course_evaluation_questions()->delete();

        return $this->sendResponse([], $course_evaluation->title . ' has been deleted.', 200);
    }

    public function showCourseEvaluationRespondents(Request $request, $course_evaluation_id)
    {
        $searchParts = explode(' ', request('search'));

        $course_evaluation = CourseEvaluation::select(['id', 'course_id'])->find($course_evaluation_id);

        if (!$course_evaluation) {
            return $this->sendResponse([], 'No course evaluation found.', 404);
        }

        $users = User::when(!empty(request('search')), function ($query) use ($searchParts) {
                Helper::queryNameSearch($searchParts, $query);
            })
            ->whereHas('enrollments', function($query) use ($course_evaluation) {
                $query->where('course_id', $course_evaluation->course_id);
            })
            ->whereHas('student_course_evaluations', function ($subquery) use ($request, $course_evaluation_id) {
                $subquery->where('course_evaluation_id', $course_evaluation_id)
                    ->when(($request->submitted_from || $request->submitted_to), function ($query) use ($request) {
                        FilterHelper::queryCreatedAt($query, $request->submitted_from, $request->submitted_to);
                    });
            })
            ->with([
                'student_course_evaluations' => function ($student_course_evaluations) use ($course_evaluation_id) {
                    return $student_course_evaluations->with([
                            'answers' => function ($answers) {
                                return $answers->with(['course_evaluation_question']);
                            }
                        ])
                            ->where('course_evaluation_id', $course_evaluation_id)
                            ->orderBy('id', 'DESC')
                            ->get();
                }
            ])
            ->paginate(request('paginate'));

        $usersResource = StudentCourseEvaluationResource::collection($users);


        $users->data = $usersResource;


        return $this->sendResponse($users, 'List of course evaluation respondents.', 200);
    }

    public function showCourseEvaluationRespondentAttempts($course_evaluation_id, $user_id)
    {
        $user = User::with([
            'student_course_evaluations' => function ($student_course_evaluations) use ($course_evaluation_id) {
                return $student_course_evaluations->where('course_evaluation_id', $course_evaluation_id);
            }
        ])
            ->where('id', $user_id)
            ->first();

        return $this->sendResponse($user, 'List of course evaluation respondent attempts.', 200);
    }

    public function showCourseEvaluationRespondentAnswers($student_course_evaluation_id, $user_id)
    {
        $user = User::with([
                'student_course_evaluations' => function ($student_course_evaluations) use ($student_course_evaluation_id) {
                    return $student_course_evaluations->with([
                        'answers' => function ($answers) {
                            $answers->with('image');
                        }
                    ])
                        ->where('id', $student_course_evaluation_id)
                        ->has('answers');
                }
            ])
            ->where('id', $user_id)
            ->first();

        return $this->sendResponse($user, 'List of course evaluation respondents.', 200);
    }

    public function updateStudentCourseEvaluationStatus($student_course_evaluation_id)
    {
        $student_course_evaluation = StudentCourseEvaluation::find($student_course_evaluation_id);

        if (!$student_course_evaluation) {
            return $this->sendResponse([], 'Student course evaluation not found', 404);
        }

        $student_course_evaluation->update(['is_checked' => true]);

        return $this->sendResponse($student_course_evaluation, 'Student course evaluation checked.', 200);
    }

    public function courseEvaluationAnswers(Request $request, $course_evaluation_id)
    {

        $studentCourseEvaluations = StudentCourseEvaluation::where('course_evaluation_id', $course_evaluation_id)
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

        $answers = collect($studentCourseEvaluations->pluck('answers')->flatten())->groupBy('course_evaluation_question_id');

        return $this->sendResponse($answers, 'List of course evaluation respondents answer summary', 200);
    }

    public function updateCourseEvaluationValidity(UpdateCourseEvaluationValidityRequest $request, $course_evaluation_id)
    {
        request()->validate([
            'date' => 'in:anytime,' . date('Y-m-d', strtotime(request('date')))
        ]);

        $course_evaluation = CourseEvaluation::find($course_evaluation_id);

        if (!$course_evaluation) {
        return $this->sendResponse([], 'No course evaluation found.', 404);
        }

        $course_evaluation->update([
            'validity' => $request->validity,
            'available_from' => $request->available_from,
            'available_to' => $request->available_to,
        ]);

        return $this->sendResponse($course_evaluation, 'Course Evaluation validity has been updated.', 200);
    }

    public function sortQuestions(CourseEvaluationQuestionSortRequest $request){
        
        foreach ($request->course_evaluation_question_id as $key => $course_evaluation_question_id) {
           CourseEvaluationQuestion::where('id', $course_evaluation_question_id)->update(['sort_no' => $key]);
        }
    
        $course_evaluation_questions = CourseEvaluationQuestion::where('course_evaluation_id', $request->course_evaluation_id)->orderBy('sort_no', 'ASC')->get();
    
        return $this->sendResponse($course_evaluation_questions, 'You have successfully sort course evaluation questions.', 200);
        
    }
}
