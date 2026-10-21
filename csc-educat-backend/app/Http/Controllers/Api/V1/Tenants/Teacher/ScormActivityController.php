<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\ScormActivity;
use App\Models\Enrollment;
use App\Models\ScormActivityScore;
use App\Models\ScormActivityAnswer;
use App\Models\ScormQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exports\Tenants\Teacher\GradingScormActivityRespondentsList;
use App\Exports\Tenants\Teacher\GradingScormActivityRespondentsAnswers;

use Log;

use Carbon\Carbon;

class ScormActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function index(Request $request)
    {
        $scorm_activity = ScormActivity::all();
    }

    public function show($course_id, $scorm_activity_id)
    {
        $scorm_activity = ScormActivity::where('course_id', $course_id)->where('id', $scorm_activity_id)->first();
        

        if (!$scorm_activity) {
            return $this->sendResponse([], 'No scorm activity found.', 404);
        }

        return $this->sendResponse($scorm_activity, 'Scorm Activity details', 200);
    }
    
    public function getScormActivityQuestions($course_id, $scorm_activity_id)
    {
        $scorm_activity = ScormActivity::where('course_id', $course_id)->where('id', $scorm_activity_id)->first();
        
        if (!$scorm_activity) {
            return $this->sendResponse([], 'No scorm activity found.', 404);
        }

        $scorm_questions = ScormQuestion::where('scorm_activity_id', $scorm_activity_id)->get();

        return $this->sendResponse($scorm_questions, 'Scorm Activity questions', 200);
    }
    
    public function getScormActivityQuestionAnswers($course_id, $scorm_activity_id, $question_id)
    {
        $scorm_activity = ScormActivity::where('course_id', $course_id)->where('id', $scorm_activity_id)->first();
        
        if (!$scorm_activity) {
            return $this->sendResponse([], 'No scorm activity found.', 404);
        }

        $scorm_questions = ScormActivityAnswer::where('scorm_activity_id', $scorm_activity_id)->where('question_id', $question_id)
            ->whereHas('user')
            ->get();

        return $this->sendResponse($scorm_questions, 'Scorm Activity question answer', 200);
    }

    public function getScormActivityRespondents($course_id, $scorm_activity_id, Request $request)
    {
        $searchParts = explode(' ', $request->search);

        $scorm_scores = ScormActivityScore::where('course_id', $course_id)
            ->where('scorm_activity_id', $scorm_activity_id)
            ->with('user', function($user) {
                $user->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
            })
            ->whereHas('user', function($query) use ($request, $searchParts){
                $query->when(!empty($request->search), function ($query) use ($request, $searchParts) {
                    Helper::queryNameSearch($searchParts, $query);
                });
            })
            ->when(($request->submitted_from || $request->submitted_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->submitted_from, $request->submitted_to);
            })
            ->paginate($request->paginate);

        return $this->sendResponse($scorm_scores, 'List of scorm activity respondents', 200);
    }

    public function getLearnerScormActivityAnswers($course_id, $scorm_activity_id, $user_id, Request $request)
    {
        $enrolled = Enrollment::where('user_id', $user_id)->where('course_id', $course_id)->exists();

        if(!$enrolled) {
            return $this->sendResponse([], 'Learner not enrolled', 404);
        }

        $user = User::select(['id', 'first_name', 'last_name', 'suffix'])->find($user_id);

        $scorm_activity = ScormActivity::where('course_id', $course_id)->where('id', $scorm_activity_id)->first();

        if (!$scorm_activity) {
            return $this->sendResponse([], 'No scorm activity found.', 404);
        }

        $scorm_scores = ScormActivityScore::where('course_id', $course_id)
            ->where('scorm_activity_id', $scorm_activity_id)
            ->whereHas('scorm_activity')
            ->with('scorm_activity')
            ->where('user_id', $user_id)
            ->when(($request->submitted_from || $request->submitted_to), function ($query) use ($request) {
                FilterHelper::queryCreatedAt($query, $request->submitted_from, $request->submitted_to);
            })
            ->first();

        $scorm_answers = ScormQuestion::where('scorm_activity_id', $scorm_activity_id)
            ->with(['scorm_activity_answers' => function($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->get();
        
        $data = [
            'user' => $user,
            'score' => $scorm_scores,
            'answers' => $scorm_answers
        ];

        return $this->sendResponse($data, 'List of scorm activity learner answers', 200);
    }

    public function scormActivityRespondentsExport($course_id, $scorm_activity_id, Request $request){

        return new GradingScormActivityRespondentsList($course_id, $scorm_activity_id, $request->search, $request->submitted_from, $request->submitted_to);  

    }

    public function scormActivityRespondentsAnswersExport($course_id, $scorm_activity_id, Request $request){

        return new GradingScormActivityRespondentsAnswers($course_id, $scorm_activity_id, $request->search, $request->submitted_from, $request->submitted_to);  

    }
}