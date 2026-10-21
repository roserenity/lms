<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use App\Models\CourseEvaluation;
use App\Models\StudentCourseEvaluation;
use App\Models\StudentModuleProgress;
use Illuminate\Http\Request;
use App\Services\Tenants\ImageService;
use Illuminate\Support\Facades\Log;

use App\Models\StudentCourseEvaluationAnswer;

class CourseEvaluationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function show($course_evaluation_id)
    {
        $course_evaluation = CourseEvaluation::with([
            'course_evaluation_questions' => function ($course_evaluation_questions) {
                return $course_evaluation_questions->with([
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
            ->find($course_evaluation_id);

        if (!$course_evaluation) {
            return $this->sendResponse([], 'No course evaluation found.', 404);
        }

        return $this->sendResponse($course_evaluation, 'Course evaluation.', 200);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'course_evaluation_id.*' => 'required|exists:course_evaluations,id',
                'course_evaluation_question_id.*' => 'required|exists:course_evaluation_questions,id',
                'answer.*' => 'nullable',
                'file_attachment.*' => 'nullable|sometimes|mimes:jpg,jpeg,png,doc,docx,ppt,pptx,pdf'
            ]);

            if ($request->has('course_evaluation_id') && !empty($request->course_evaluation_id))
            {
                $student_course_evaluation = StudentCourseEvaluation::create([
                    'user_id' => $this->authUser()->id,
                    'course_evaluation_id' => $request->course_evaluation_id[0]
                ]);
            } else {
                return $this->sendResponse([], 'Course evaluation ID is required.', 400);
            }
            
            $answers = [];
            foreach ($request->course_evaluation_question_id as $key => $question_id) {
                $student_course_evaluation_answer = StudentCourseEvaluationAnswer::create([
                    'user_id' => $this->authUser()->id,
                    'student_course_evaluation_id' => $student_course_evaluation->id,
                    'course_evaluation_question_id' => $question_id,
                    'data' => $request->answer[$key]
                ]);

                $answers[] = $student_course_evaluation_answer;

                if ($request->hasFile('file_attachment.'.$key)) {
                    ImageService::uploadCourseEvaluationAnswerFileAttachment([
                        'model_id' => $student_course_evaluation_answer->id,
                        'model_type' => 'App\\Models\\StudentCourseEvaluationAnswer',
                        'file_attachment' => $request->file('file_attachment')[$key]
                    ]);
                }
            }
    

            StudentModuleProgress::updateOrCreate([
                'model_id' => $request->course_evaluation_id[0],
                'model_type' => 'App\\Models\\CourseEvaluation',
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
            if (isset($student_course_evaluation)) {
                $student_course_evaluation->delete();
            }

            Log::error('Course Evaluation Submission Error: ' . $e);
            return $this->sendResponse([], 'Unexpected response.', 422);
        }
    }

    public function getCourseEvaluation($course_evaluation_id)
    {
        $course_evaluation = CourseEvaluation::where('id', $course_evaluation_id)->withCount([
            'student_course_evaluations' => function ($student_course_evaluations) {
                return $student_course_evaluations->where('user_id', $this->authUser()->id);
            }, 
            'course_evaluation_questions'
        ])
        ->with([
            'student_course_evaluations' => function ($student_course_evaluations) {
                return $student_course_evaluations->where('user_id', $this->authUser()->id)->orderBy('id', 'DESC');
            }
        ])->first();

        if(!$course_evaluation) {
            return $this->sendResponse([], 'Course evaluation not found.', 404);
        }

        if ($course_evaluation->is_shuffle_questions && $course_evaluation->course_evaluation_questions->isNotEmpty()) {
            $course_evaluation->course_evaluation_questions = $course_evaluation->course_evaluation_questions->shuffle();
        }
        
        return $this->sendResponse($course_evaluation, 'Show learner course evaluations', 200);
    }
}
