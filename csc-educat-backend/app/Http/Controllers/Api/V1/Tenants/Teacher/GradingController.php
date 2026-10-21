<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\GroupActivity;
use App\Models\AssessmentScore;
use App\Models\Course;
use App\Models\CourseEvaluation;
use App\Models\ModuleEvaluation;
use App\Models\QuestionAnswer;
use App\Models\QuestionAnswerFeedback;
use App\Models\StudentCourseEvaluation;
use App\Models\StudentModuleEvaluation;
use App\Models\GroupActivityGroup;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Models\Scorm;
use App\Models\StudentModuleProgress;
use App\Models\StudentCourseProgress;
use DB;

use Log;

use Illuminate\Pagination\LengthAwarePaginator;

class GradingController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.instructor');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    if(request('paginate')){
      $courses = Course::when(!empty(request('search')), function ($query) use ($request) {
          FilterHelper::queryCourseTitleAndCodeSearch($request->search, $query);
        })
        ->select(['id', 'title', 'created_at', 'status', 'type_of_learning', 'available_until', 'course_code'])
        ->when($request->status, function($query) use ($request){
          $query->where('status', $request->status);
        })
        ->when($request->type_of_learning, function($query) use ($request){
          $query->where('type_of_learning', $request->type_of_learning);
        })
        ->when(($request->created_from || $request->created_to), function ($query) use ($request) {
          FilterHelper::queryAvailabilityDates($query, $request->created_from, $request->created_to);
        })
        ->withCount(['assessments', 'course_evaluations', 'module_evaluations', 'scorm', 'group_activities'])
        ->orderBy('created_at', 'DESC')
        ->paginate(request('paginate'));
    } else {
      $courses = Course::where('status', Course::IS_PUBLISH)->withCount(['course_evaluations', 'module_evaluations', 'scorm'])->get();
    }

    $courses->each(function ($course) {
        $course->evaluations_count = $course->course_evaluations_count + $course->module_evaluations_count;
    });

    $courses
    ->makeHidden([
      'user_is_enrolled',
      'course_is_open_for_registration',
      'course_card',
      'image'
    ]);

    return $this->sendResponse($courses, 'Lists of grading courses list.', 200);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {

    $course = Course::find($id);

    if (!$course) {
      return $this->sendResponse([], 'No course exists', 404);
    }

    $course_evaluations = [];
    $module_evaluations = [];
    $assessments = [];
    $scorms = [];
    $group_activities = [];

    if(request('type') == 'evaluation') {
      $course_evaluations = CourseEvaluation::select(['id', 'title', 'course_id'])
        ->where('course_id', $id)->with([
        'student_course_evaluations' => function ($query) use ($course) {
          return $query->whereHas('user', function($subQuery) use ($course) {
            FilterHelper::filterEnrolledUsers($subQuery, $course->id);
          })->latest()->distinct('user_id');
        }
      ])
      ->when(!empty(request('search')), function ($query) {
        $query->where('title', 'LIKE', '%' . request('search') . '%');
      })->get();
    }

    if(request('type') == 'evaluation') {
      if($course && $course->modules) {
        $module_ids = $course->modules->pluck('id');
        
        $module_evaluations = ModuleEvaluation::select(['id', 'title', 'module_id'])
        ->with([
          'student_module_evaluations' => function ($query) use ($course) {
            return $query->whereHas('user', function($subQuery) use ($course) {
              FilterHelper::filterEnrolledUsers($subQuery, $course->id);
            })->latest()->distinct('user_id');
          }
        ])
        ->whereIn('module_id', $module_ids)
        ->when(!empty(request('search')), function ($query) {
          $query->where('title', 'LIKE', '%' . request('search') . '%');
        })
        ->get();
      }
    }

    if(request('type') == 'assessment') {
      $assessments = Assessment::select(['id', 'title', 'course_id', 'type_of_assessment'])->where('course_id', $id)->withCount([
        'assessment_scores' => fn ($assessment_score) => $assessment_score->where('active', 1)->whereHas('user', fn($subQuery) => FilterHelper::filterEnrolledUsers($subQuery, $course->id))
      ])
      ->when(!empty(request('search')), function ($query) {
        $query->where('title', 'LIKE', '%' . request('search') . '%');
      })->get();
    }
      
    if(request('type') == 'scorm') {
      $scorms = Scorm::where('course_id', $id)
        ->when(!empty(request('search')), function ($query) {
          $query->where('title', 'LIKE', '%' . request('search') . '%');
        })
        ->with(['model'])
        ->withCount(['learner_registrations' => function ($query) use ($course) {
            return $query->whereHas('user', function($subQuery) use ($course) {
              FilterHelper::filterEnrolledUsers($subQuery, $course->id);
            });
          }
        ])->get();
    }
      
    if(request('type') == 'group_activity') {
      $group_activities = GroupActivity::where('course_id', $id)->when(!empty(request('search')), function ($query) {
          $query->where('title', 'LIKE', '%' . request('search') . '%');
        })->get()
        ->map(function ($activity) {
            $activity->no_of_members = $activity->group_activity_groups
                ->flatMap(fn ($group) => $group->group_activity_has_group_members)
                ->unique('id')
                ->count();
    
            return $activity;
        });

      $group_activities->makeHidden(['course_card']);
    }

    $mergedResults = collect([]);

    if (collect($course_evaluations)->isNotEmpty()) {
        $mergedResults = $mergedResults->merge($course_evaluations);
    }

    if (collect($assessments)->isNotEmpty()) {
        $mergedResults = $mergedResults->merge($assessments);
    }

    if (collect($module_evaluations)->isNotEmpty()) {
      $mergedResults = $mergedResults->merge($module_evaluations);
    }

    if (collect($scorms)->isNotEmpty()) {
      $mergedResults = $mergedResults->merge($scorms);
    }

    if (collect($group_activities)->isNotEmpty()) {
      $mergedResults = $mergedResults->merge($group_activities);
    }

    $perPage = request('paginate', 10);
    $page = request('page', 1);

    $paginatedResults = new LengthAwarePaginator(
        $mergedResults->forPage($page, $perPage),
        $mergedResults->count(),
        $perPage,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    if($page > 1){
      $data = $paginatedResults->items();
      $valuesArray = [];
      foreach ($data as $value) {
          $valuesArray[] = $value;
      }
      $paginatedResults->setCollection(collect($valuesArray));
    }
    
    $paginatedResults->course_title = $course->title;

    return $this->sendResponse(['course_title' => $course->title, 'paginated_result'=> $paginatedResults ], 'Lists of '. request('type') .' under ' . $course->title, 200);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
  }

  public function showGradingAssessment(Request $request, $assessment_id)
  {

    $searchParts = explode(' ', $request->search);

    $assessment = Assessment::where('id', $assessment_id)->first();

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $assessment = Assessment::where('id', $assessment_id)
    ->with([
      'course',
      'assessment_scores' => function ($score) use ($searchParts, $request, $assessment) {
          $score
          ->where('active', 1)
          ->whereHas('user', function ($query) use ($searchParts, $request, $assessment) {
            FilterHelper::filterEnrolledUsers($query, $assessment->course_id);

            $query->when($request->search, function ($subQuery) use ($searchParts) {
              Helper::queryNameSearch($searchParts, $subQuery);
            });
          })
          ->when(($request->submitted_from || $request->submitted_to), function ($query) use ($request) {
            FilterHelper::queryCreatedAt($query, $request->submitted_from, $request->submitted_to);
          })
          ->with(['user' => fn($query) => $query->select(['id', 'first_name', 'last_name', 'suffix'])]);
        }
      ])
      ->withCount('assessment_questions')
      ->withSum('assessment_questions', 'score')
      ->first();    

    $perPage = request('paginate');
    $page = request('page');

    $assessmentScores = $assessment->assessment_scores->forPage($page, $perPage);

    $paginatedResults = new LengthAwarePaginator(
        $assessmentScores,
        $assessment->assessment_scores->count(),
        $perPage,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    if($page > 1){
      $data = $paginatedResults->items();
      $valuesArray = [];
      foreach ($data as $value) {
          $valuesArray[] = $value;
      }
      $paginatedResults->setCollection(collect($valuesArray));
    }

    $assessment->unsetRelation('assessment_scores');

    return $this->sendResponse(['assessment_scores' => $paginatedResults, 'assessment' => $assessment, 'course' => $assessment->course->title], 'Lists of all users with their assessments under ' . $assessment->title ?? '', 200);
  }

  public function check_assessment(Request $request)
  {
    $request->validate([
      'user_id' => 'required|exists:users,id',
      'assessment_id' => 'required|exists:assessments,id',
      'user_assessment_id' => 'required|exists:assessment_scores,id',
    ]);

    $score = AssessmentScore::with('user')
      ->where('user_id', $request->user_id)
      ->where('id', $request->user_assessment_id)
      ->where('active', 1)
      ->first();

    if (!$score) {
      return $this->sendResponse([], 'No score available.', 404);
    }

    $assessment = Assessment::with([
      'question_answers' => function ($question_answers) use ($score) {
        $question_answers->with([
          'question_answer_feedback',
          'assessment_question' => function ($assessment_question) {
            $assessment_question->with('assessment_answer');
          },
        ])
          ->whereIn('id', $score->question_answer_ids)
          ->where('user_id', $score->user_id);
      },
    ])->withSum('assessment_questions', 'score')->find($request->assessment_id);

    $assessment['score'] = $score;

    return $this->sendResponse($assessment, 'Student Assessment answers.', 200);
  }

  public function updateGradingAssessmentStatus(Request $request)
  {
    $request->validate([
      'id' => 'required|exists:assessments,id',
      'assessment_scores_id.*' => 'required|exists:assessment_scores,id',
      'status' => 'required|boolean',
    ]);

    foreach($request->assessment_scores_id as $item) {
      AssessmentScore::where('id', $item)->where('assessment_id', $request->id)
      ->where('active', true)->update(['checked' => $request->status]);
    }

    return $this->sendResponse([], 'Updated!', 200);
  }

  public function updateGradingCourseEvaluationStatus(Request $request)
  {
    $request->validate([
      'id' => 'required|exists:course_evaluations,id',
      'student_evaluation_id.*' => 'required|exists:student_course_evaluations,id',
      'status' => 'required|boolean',
    ]);

    foreach($request->student_evaluation_id as $item) {
      StudentCourseEvaluation::where('id', $item)->where('course_evaluation_id', $request->id)
        ->update(['is_checked' => $request->status]);
    }

    return $this->sendResponse([], 'Updated!', 200);
  }

  public function updateGradingModuleEvaluationStatus(Request $request)
  {
    $request->validate([
      'id' => 'required|exists:module_evaluations,id',
      'student_evaluation_id.*' => 'required|exists:student_module_evaluations,id',
      'status' => 'required|boolean',
    ]);

    foreach($request->student_evaluation_id as $item) {
      StudentModuleEvaluation::where('id', $item)->where('module_evaluation_id', $request->id)
        ->update(['is_checked' => $request->status]);
    }

    return $this->sendResponse([], 'Updated!', 200);
  }

  public function updateGradingScore(Request $request)
  {
    $request->validate([
      'question_answer_id.*' => 'required|exists:question_answers,id',
      'score.*' => 'required|numeric',
      'user_id.*' => 'required|exists:users,id',
      'assessment_id.*' => 'required|exists:assessments,id',
      'checked.*' => 'required|boolean',
      'comment.*' => 'nullable'
    ]);

    foreach ($request->question_answer_id as $key => $question_answer_id) {

      $question_answer = QuestionAnswer::where('id', $question_answer_id)->first();
      $question_answer->score = $request->score[$key];
      $question_answer->save();

      QuestionAnswerFeedback::updateOrCreate(
        ['question_answer_id' => $question_answer->id, 'user_id' => $this->authUser()->id],
        ['comment' => $request->comment[$key]]
      );
    }

    $total_score = QuestionAnswer::whereIn('id', $request->question_answer_id)->sum('score');

    AssessmentScore::where('user_id', $request->user_id)->where('assessment_id', $request->assessment_id)
      ->where('active', true)->update(['score' => $total_score, 'checked' => $request->checked]);

    return $this->sendResponse([], 'Score updated!', 200);
  }

  public function showGradingStudents($assessment_id){
    $assessment = Assessment::with([
      'assessment_scores' => fn ($score) => $score->whereHas('user')->with('user')
        ->where('active', 1)
        ->whereHas('user')
        ->orderBy('created_at', 'desc')->get(),
    ])
      ->withCount('assessment_questions')
      ->withSum('assessment_questions', 'score')
      ->where('id', $assessment_id)
      ->first();

    return $this->sendResponse($assessment, 'Lists of all users under ' . $assessment->title, 200);

  }

  public function showGradingGroupActivityGroup($group_activity_id, $group_id)
  {

    $group_activity = GroupActivity::where('id', $group_activity_id)->with('group_activity_questions')->first();

    if($group_activity) {

      $group_activity_group = GroupActivityGroup::withCount('group_activity_has_group_members')->findOrFail($group_id);

      $group_activity_question = $group_activity->group_activity_questions->first();

      if($group_activity_question) {
        return $this->sendResponse(['group_activity_title' => $group_activity->title, 'group_activity_group' => $group_activity_group, 'group_activity_question' => $group_activity_question->question], 'Show all group under group activity', 200);
      }

      return $this->sendResponse(['group_activity_title' => $group_activity->title, 'group_activity_group' => $group_activity_group, 'group_activity_question' => ''], 'Show all group under group activity', 200);


    } else {

      return $this->sendResponse([], 'No group activity found.', 404);

    }
    
  }

  public function showGradingGroupActivityGroups($group_activity_id, Request $request)
  {

    $group_activity = GroupActivity::where('id', $group_activity_id)->first();

    $course = Course::find($group_activity->course_id);

    if($group_activity && $course) {

      $group_activity_groups = GroupActivityGroup::where('group_activity_id', $group_activity_id)->withCount('group_activity_has_group_members')
        ->when(!empty(request('search')), function ($query) {
          $query->where('name', 'LIKE', '%' . request('search') . '%');
        })->paginate($request->paginate);
      
      return $this->sendResponse(['course_title' => $course->title,'group_activity_title' => $group_activity->title, 'groups' => $group_activity_groups], 'Show all groups under group activity', 200);

    } else {
      return $this->sendResponse([], 'No group activity found.', 404);
    }
    
  }

  public function updateGradingGroupActivityStatus($group_activity_id, $group_id, Request $request)
  {

    $group_activity = GroupActivity::where('id', $group_activity_id)->first();

    if($group_activity) {

      $group_activity_group = GroupActivityGroup::with('group_activity_has_group_members')->findOrFail($group_id);
      
      $group_activity_group->update(['checked' => $request->checked]);

      foreach($group_activity_group->group_activity_has_group_members as $group_member) {
        if($group_activity_group->checked) {
          StudentModuleProgress::firstOrCreate([
              'course_id' => $group_activity->course_id,
              'user_id' => $group_member->id,
              'model_id' => $group_activity->id,
              'model_type' => 'App\\Models\\GroupActivity',
              'status' => StudentModuleProgress::STATUS_DONE
          ]);
        } else {
          $student_module_progress = StudentModuleProgress::where('user_id', $group_member->id)->where('course_id', $group_activity->course_id)->where('model_id', $group_activity->id)->where('model_type', 'App\\Models\\GroupActivity')->first();
          if($student_module_progress) {
            $student_module_progress->delete();
          }
        }
      }
      
      $course = Course::where('id', $group_activity->course_id)
            ->withCount(['assessments', 'topics', 'sub_topics', 'course_evaluations', 'module_evaluations', 'zoom_meetings', 'group_activities'])
            ->withCount([
                'my_assessment_score as total_checked_assessments' => function ($query) {
                    $query->where('checked', true);
                }
            ])
            ->first();
        
        if (!$course) {
            return $this->sendResponse([], 'No course found.', 404);
        }

        $user_id = $group_member->id;
        
        $progress = [
            'total_checked_assessments' => $course->total_checked_assessments ?? 0,
            'total_done_topics' => $course->getTotalDoneTopics($user_id),
            'total_done_sub_topics' => $course->getTotalDoneSubTopics($user_id),
            'total_done_assessments' => $course->getTotalDoneAssessments($user_id),
            'total_done_course_evaluations' => $course->getTotalDoneCourseEvaluations($user_id),
            'total_done_module_evaluations' => $course->getTotalDoneModuleEvaluations($user_id),
            'total_attended_zoom_meetings' => $course->getTotalAttendedZoomMeetings($user_id),
            'total_done_group_activities' => $course->getTotalDoneGroupActivities($user_id),
            'total_time_spent' => $course->getTotalTimeSpent($course->id, $user_id),
            'topics_count' => $course->topics_count,
            'sub_topics_count' => $course->sub_topics_count,
            'assessments_count' => $course->assessments_count,
            'course_evaluations_count' => $course->course_evaluations_count,
            'module_evaluations_count' => $course->module_evaluations_count,
            'zoom_meetings_count' => $course->zoom_meetings_count,
            'group_activities_count' => $course->group_activities_count,
        ];

        $progress_percent = $course->type_of_learning === 'BLENDED' ? Helper::calculateLearnerProgressPercentBlended((object)$progress) : Helper::calculateLearnerProgressPercentElearning((object)$progress);

        StudentCourseProgress::updateOrCreate([
            'user_id' => $user_id,
            'course_id' => $course->id
        ], [
            ... $progress,
            'progress' => $progress_percent
        ]);
      
      return $this->sendResponse([], 'Group status checked updated', 200);

    } else {

      return $this->sendResponse([], 'No group activity found.', 404);

    }
    
  }

}
