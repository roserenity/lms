<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScormActivity;
use App\Models\ScormQuestion;
use App\Models\ScormActivityAnswer;
use App\Models\ScormActivityScore;
use App\Models\ScormStudentProgress;

class ScormController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function storeScormSubmission(Request $request)
    {

      $attributes = [
          'course_id' => $request->course_id,
          'model_id' => $request->model_id,
          'model_type' => $request->type === 'topic' ? 'App\Models\Topic' : 'App\Models\SubTopic',
      ];
      
      if (!empty($request->activity_id)) {
          $attributes['activity_id'] = $request->activity_id;
      }
      
      $scorm = ScormActivity::firstOrCreate(
        $attributes,
        [
          'title' => $request->title,
          'passing_score' => $request->passing_score
        ]
      );

      $question = ScormQuestion::firstOrCreate(
        [ 
          'scorm_activity_id' => $scorm->id,
          'question_uuid' => $request->question_uuid,
        ],
        [
          'question' => $request->question,
          'type_of_question' => $request->type_of_question,
          'choices' => $request->choices ?? null,
          'correct_answers' => $request->correct_answers ?? null
        ]
      );

      $existingAnswer = ScormActivityAnswer::where([
          'scorm_activity_id' => $scorm->id,
          'question_id' => $question->id,
          'user_id' => $this->authUser()->id
      ])->first();

      if ($existingAnswer) {
          return $this->sendResponse($scorm, 'Answer already submitted!', 422);
      }

      ScormActivityAnswer::firstOrCreate(
        [ 
          'scorm_activity_id' => $scorm->id,
          'question_id' => $question->id,
          'user_id' => $this->authUser()->id
        ],
        [
          'answer' => $request->answer,
          'is_correct' => $request->is_correct
        ]
      );

      if($request->type_of_question === 'QA_SECTION')
      {
        ScormActivityScore::firstOrCreate(
          [
            'course_id' => $request->course_id,
            'scorm_activity_id' => $scorm->id,
            'user_id' => $this->authUser()->id
          ],
          [
            'score' => null,
            'passed' => false
          ]
        );
      }

      return $this->sendResponse($scorm, 'Answer saved!', 200);
    }

    public function storeScormScore(Request $request)
    {
      $request->validate([
        'course_id' => 'required|exists:courses,id',
        'scorm_activity_id' => 'required|exists:scorm_activities,id',
        'score' => 'required'
      ]);

      ScormActivityScore::firstOrCreate(
        [
          'course_id' => $request->course_id,
          'scorm_activity_id' => $request->scorm_activity_id,
          'user_id' => $this->authUser()->id,
          'passed' => $request->passed
        ],
        [
          'score' => $request->score
        ]
      );

      return $this->sendResponse([], 'Score saved!', 200);
    }

    public function storeScormProgress(Request $request)
    {
      $request->validate([
        'course_id' => 'required|exists:courses,id',
        'model_id' => 'required',
        'model_type' => 'required',
        'scorm_course_progress' => 'required',
      ]);

      ScormStudentProgress::updateOrCreate(
        [
          'course_id' => $request->course_id,
          'model_id' => $request->model_id,
          'model_type' => $request->model_type === 'topic' ? 'App\Models\Topic' : 'App\Models\SubTopic',
          'user_id' => $this->authUser()->id,
        ],
        [
          'scorm_course_progress' => $request->scorm_course_progress,
        ]
      );

      return $this->sendResponse([], 'Scorm Progress saved!', 200);
    }

    public function getScormProgress(Request $request)
    {
      $request->validate([
        'course_id' => 'required|exists:courses,id',
        'model_id' => 'required',
        'model_type' => 'required',
      ]);

      $progress = ScormStudentProgress::where([
          'course_id' => $request->course_id,
          'model_id' => $request->model_id,
          'model_type' => $request->model_type === 'topic' ? 'App\Models\Topic' : 'App\Models\SubTopic',
          'user_id' => $this->authUser()->id,
      ])->first();

      return $this->sendResponse($progress, 'Return scorm progress', 200);
    }
}
