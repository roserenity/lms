<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentScore;
use App\Models\QuestionAnswer;
use App\Models\StudentModuleProgress;
use App\Models\Rubric;
use Illuminate\Support\Facades\Storage;

use Log;
class AssessmentController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.user');
  }

  public function showAssessment($assessment_uuid)
  {
    $user_id = $this->authUser()->id;
    $assessment = Assessment::with([
      // 'assessment_questions' => fn ($assessment_questions) => $assessment_questions->with(
      //   [
      //     'assessment_answer' => function ($assessment_answer) {
      //       $assessment_answer->select(['assessment_question_id', 'choices', 'id', 'uuid', 'created_at']);
      //     }
      //   ]
      // ),
      'assessment_scores' => function($assessment_scores)
      {
        return $assessment_scores->where('user_id', $this->authUser()->id)
        ->latest()
        ->first();
      }
    ])
      // ->with(['assessment_scores' => function($assessment_scores) {
      //   $assessment_scores->groupBy('user_id');
      // }])
      ->withCount('assessment_questions')
      ->where('uuid', $assessment_uuid)
      ->first();

    if (!$assessment) {
      return $this->sendResponse([], 'Assessment not found.', 404);
    }

    //get assessment based on accepted score
    $assessment_scores = AssessmentScore::where('assessment_id', $assessment->id)
      ->where('user_id', $user_id);

    $attempts = $assessment_scores->count();

    $accepted_score = $assessment_scores->where('active', 1)->first();

    
    $assessment['assessment_questions'] = AssessmentQuestion::with(['assessment_answer' => function ($assessment_answer) use ($accepted_score) {
      if($accepted_score) {
        //if($accepted_score->checked === 1) {
          $assessment_answer->select(['assessment_question_id', 'correct_answer', 'choices', 'id', 'uuid', 'created_at']);
        //} else {
        //  $assessment_answer->select(['assessment_question_id', 'choices', 'id', 'uuid', 'created_at']);
        //}
      } else {
        $assessment_answer->select(['assessment_question_id', 'choices', 'id', 'uuid', 'created_at']);
      }
    }])->where('assessment_id', $assessment->id)->orderBy('sort_no', 'asc')->get();

    $percentage = $accepted_score->assessment_score_percentage ?? 0;

    $assessment['assessment_score'] = [
        'score' => $accepted_score->score ?? 0,
        'attempts' => $attempts,
        'created_at' => $accepted_score->created_at ?? null,
        'percentage' => $percentage,
        'status' => $assessment->passing_score ? (intval($assessment->passing_score) > $percentage ? 'FAILED' : 'PASSED') : 'FAILED'
    ];

    if($assessment->shuffle_questions){
        $questions = $assessment->assessment_questions;
        if($questions->count() > 0) {
          $assessment->assessment_questions = $questions->shuffle();
        }
    }
    
    foreach($assessment->assessment_questions as $question) {
      if($question->rubric_id) {
        $question->rubric = Rubric::where('id', $question->rubric_id)->with(['ratings', 'criterias'])->first();
      }
      
      if ( $question->type_of_question === 'ordering' || $assessment->shuffle_answers) {
        $choices = collect($question->assessment_answer->choices);
        if($choices->isNotEmpty()) {
          $question->assessment_answer->choices = $choices->shuffle();
        }
      }
    }

    return $this->sendResponse($assessment, 'Assessment details.', 200);
  }

  public function getAssessmentQuestions($assessment_uuid)
  {
    $assessment = Assessment::where('uuid', $assessment_uuid)->first();

    if (!$assessment) {
      return $this->sendResponse([], 'Assessment not found.', 404);
    }

    if ($assessment->shuffle_questions) {
      $questions = $assessment->assessment_questions->shuffle();
    } else {
      $questions = $assessment->assessment_questions;
    }

    return $this->sendResponse($questions, 'Assessment shuffled questions.', 200);
  }

  public function fileUpload($question, $file)
  {
    $path = "file-assessment/{$this->authUser()->uuid}/question/{$question->uuid}";

    $file_path = Storage::disk('s3')->put($path, $file);

    $url = Storage::disk('s3')->url($file_path);

    return $url;
  }

  //saving bulk answer or 1 time saving
  public function storeAssessmentAnswers(Request $request)
  {
    $request->validate([
      'file.*' => 'nullable|mimes:jpg,jpeg,png,doc,docx,ppt,pptx,pdf'
    ]);

    $data = [];

    foreach ($request->question_id as $key => $value) {

      $question = AssessmentQuestion::with('assessment_answer')
        ->find($value);

      $url = NULL;

      if($question) {
        if ($question->require_file_attachment) {
          if (!empty($request->file[$key]) && $request->file[$key] != 'null') {
            $url = $this->fileUpload($question, $request->file[$key]);
          }
        }
  
        if ($question->type_of_question == 'true_or_false' || $question->type_of_question == 'multiple_choice' || $question->type_of_question == 'ordering') {
          if ( $request->answer[$key] && $question->assessment_answer->correct_answer == $request->answer[$key]) {
            $score = $question->score;
          } else {
            $score = 0;
          }
        } else if ($question->type_of_question == 'identification') {
          $score = 0;
          foreach ($question->assessment_answer->choices as $choice_item) {
            foreach ($choice_item as $_key => $_value) {
                if ($request->answer[$key] && $request->answer[$key] == $_value) {
                  $score = $question->score;
                }
            }
          }
        } else {
          $score = 0;
        }
  
        $question_answer = QuestionAnswer::create([
          'user_id' => $this->authUser()->id,
          'assessment_id' => $request->assessment_id,
          'assessment_question_id' => $question->id,
          'answer' => $request->answer[$key] ?? NULL,
          'support_answer' => $request->support_answer[$key] ?? NULL,
          'file' => $url,
          'score' => $score
        ]);
  
        // collate score and the latest answer id
        $data[$key] = [
          'score' => $score,
          'url' => $url,
          'question_answer_id' => $question_answer->id
        ]; 
      } else {
        return $this->sendResponse([], 'Assessment Question not found.', 404);
      }
    }

    $assessment = Assessment::where('id', $request->assessment_id)->first();

    if ($assessment) {
      $total_score = collect($data)->sum('score');
      $question_answer_ids = collect($data)->pluck('question_answer_id');

      $assessment->assessment_scores()->create([
        'user_id' => $this->authUser()->id,
        'score' => $total_score,
        'post_type' => $request->post_type,
        'question_answer_ids' => $question_answer_ids,
        'status' => 'PENDING'
      ]);

      // remove current active
      $assessment_scores = AssessmentScore::where('assessment_id', $assessment->id)->where('user_id', $this->authUser()->id);
      $assessment_scores->update(['active' => 0]);

      // set active assessment answers based on accepted_scores

      if (!$assessment->accepted_score || $assessment->accepted_score == 'latest') {
        $assessment_score = $assessment_scores->latest()->first();
      }

      if ($assessment->accepted_score == 'highest') {
        $assessment_score = $assessment_scores->orderBy('score', 'desc')->first();
      }

      // fix count of attempts
      $assessment_score->active = 1;
      $assessment_score->attempt = $assessment_scores->where('assessment_id', $assessment->id)->where('user_id', $this->authUser()->id)->count();
      $assessment_score->save();

      StudentModuleProgress::updateOrCreate([
        'model_id' => $assessment->id,
        'model_type' => 'App\\Models\\Assessment',
        'course_id' => $request->course_id,
        'user_id' => $this->authUser()->id ],
      [
        'status' => StudentModuleProgress::STATUS_DONE,
        'time_spent' => $request->time_spent
      ]);

      return $this->sendResponse([], 'Assessment answers saved.', 201);
    } else {
      return $this->sendResponse([], 'Assessment not found.', 404);
    }
  }
}
