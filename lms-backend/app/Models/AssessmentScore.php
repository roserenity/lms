<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Log;
use App\Models\QuestionAnswer;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentScore extends Model
{
  use HasFactory, SoftDeletes;

  const IS_PASSED = 'PASSED';
  const IS_FAILED = 'FAILED';

  protected $fillable = [
    'user_id',
    'assessment_id',
    'score',
    'post_type',
    'active',
    'attempt',
    'question_answer_ids',
    'checked',
    'status'
  ];

  protected $casts = [
    'question_answer_ids' => 'array'
  ];

  protected $appends = ['question_answer', 'assessment_score_percentage'];

  public function assessment()
  {
    return $this->belongsTo(Assessment::class, 'assessment_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function all_question_answers()
  {
    return QuestionAnswer::whereIn('id', $this->question_answer_ids)->get();
  }

  public function all_question_answers_with_questions()
  {
    
    if (empty($this->question_answer_ids)) {
      return [];
    }
    
    return QuestionAnswer::whereIn('id', $this->question_answer_ids)->with(['assessment_question' => fn ($assessment_question) => $assessment_question->with('assessment_answer')])->get();
  }


  public function question_answers($assessment_question_id)
  {
    return QuestionAnswer::whereIn('id', $this->question_answer_ids)->where('assessment_question_id', $assessment_question_id)->first();
  }

  public function getQuestionAnswerAttribute()
  {
    if (empty($this->question_answer_ids)) {
      return [];
    }
    return QuestionAnswer::where('user_id', auth()->guard('api')->id())
    ->whereIn('id', $this->question_answer_ids)->get();
  }

  public function getAssessmentScorePercentageAttribute()
  {
    if($this->assessment)
    {
      return number_format( Helper::calculatePercentage(floatval($this->score), floatval($this->assessment->total_points)), 2 );
    }

    return 0;
  }
}
