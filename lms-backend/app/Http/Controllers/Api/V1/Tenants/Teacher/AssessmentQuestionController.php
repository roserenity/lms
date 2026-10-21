<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;

use App\Http\Requests\Tenants\AssessmentQuestionRequest;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\QuestionAnswer;
use App\Models\AssessmentScore;

use Log;

class AssessmentQuestionController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.instructor');
  }

  public function index()
  {
    //
  }

  public function create()
  {
    //
  }

  public function store(AssessmentQuestionRequest $request)
  {
    $assessment = Assessment::find($request->assessment_id);

    if (! $assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $assessment_question = $assessment->assessment_questions()->create($request->safe()->only(['assessment_id', 'sort_no']));

    $assessment_question->assessment_answer()->create();

    return $this->sendResponse($assessment_question, 'You have successfully added a new question.', 201);
  }

  public function show($id)
  {
    //
  }

  public function edit($id)
  {
    //
  }

  public function update(AssessmentQuestionRequest $request, $id)
  {
    $assessment_question = AssessmentQuestion::find($id);

    if (! $assessment_question) {
      return $this->sendResponse([], 'No assessment question found.', 404);
    }

    $assessment_question->update($request->safe()->only(['question','type_of_question','score','require_file_attachment','require_support_answer', 'rubric_id']));

    $assessment_question->assessment_answer()->update($request->safe()->only(['correct_answer']) + ['choices' => json_encode($request->choices)]);

    //if score changed
    if($assessment_question->wasChanged('score') && ($assessment_question->type_of_question == 'true_or_false' || $assessment_question->type_of_question == 'multiple_choice' || $assessment_question->type_of_question == 'ordering')) {
      
      //get all user answers under that question and update the score
      $answers = QuestionAnswer::where('assessment_id', $request->assessment_id)
        ->where('assessment_question_id', $assessment_question->id)
        ->get();

      $answers->each(function ($item) use ($assessment_question) {
        if(intval($item->score) !== 0) {
          $item->score = $assessment_question->score;
          $item->save();
        }
      });


      //recalculate total scores
      $user_assessments = AssessmentScore::where('assessment_id', $assessment_question->assessment_id)->get();

      $user_assessments->each(function($item) {
        $total_score = QuestionAnswer::whereIn('id', $item->question_answer_ids)->sum('score');

        $item->score = $total_score;
        $item->save();
      });

    }

    //update Assessment total_points
    $assessment = Assessment::find($assessment_question->assessment_id);
    $sum = $assessment->assessment_questions->sum('score');
    $assessment->update(['total_points' => $sum]);
    
    return $this->sendResponse($assessment_question, 'You have successfully updated a question.', 200);
  }

  public function destroy($id)
  {
    $assessment_question = AssessmentQuestion::find($id);

    if (! $assessment_question) {
      return $this->sendResponse([], 'No assessment question found.', 404);
    }

    //recalculate total scores
    $user_assessments = AssessmentScore::where('assessment_id', $assessment_question->assessment_id)->get();

    $assessment_question->delete();
    $assessment_question->assessment_answer()->delete();
    $assessment_question->question_answer()->delete();

    $user_assessments->each(function($item) {
      $total_score = QuestionAnswer::whereIn('id', $item->question_answer_ids)->sum('score');

      $item->score = $total_score;
      $item->save();
    });

    return $this->sendResponse([], 'Assessment question has been deleted.', 200);
  }
}