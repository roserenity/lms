<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Helpers\FilterHelper;

use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;

use App\Notifications\ShareAssessmentNotification;
use App\Exports\Tenants\Teacher\GradingAssessment;
use App\Exports\Tenants\Teacher\GradingAssessmentSummary;

use App\Http\Requests\Tenants\AssessmentRequest;
use App\Jobs\Tenants\ProcessAssessmentExport;
use App\Jobs\Tenants\ProcessAssessmentSummaryExport;
use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentQuestion;
use App\Models\Course;
use App\Models\Module;
use App\Models\ShareAssessment;
use App\Models\AssessmentScore;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Exports;
use App\Models\Topic;
use App\Models\SubTopic;
use App\Models\QuestionAnswer;

class AssessmentController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.instructor');
  }

  public function index()
  {
    if(request('paginate')) {
      $assessments = Assessment::select(['id', 'title', 'course_id', 'created_at'])
        ->has('course')
        ->with(['course' => function($query) {
          $query->select(['id', 'title']);
        }, 'assessment_questions'])
        ->where('uploader_id', auth()->guard('api')->user()->id)
        ->when(request('search'), function($query){
          $query->where('title', 'LIKE', '%' . request('search') . '%');
        })
        ->when(!empty(request('date_to')) || !empty(request('date_from')), function ($query) {
          FilterHelper::queryCreatedAt($query, request('date_from'), request('date_to'));
        })
        ->latest()
        ->paginate(request('paginate'));
    } else {
      $assessments = Assessment::with(['course', 'assessment_questions'])
        ->where('uploader_id', auth()->guard('api')->user()->id)
        ->latest()
        ->get();
    }

    return $this->sendResponse($assessments, 'List of all your assessments.', 200);
  }

  public function create()
  {
    //
  }

  public function store(AssessmentRequest $request)
  {
    $course = Course::find($request->course_id);

    $course_assessment = $course->assessments()->create($request->safe()->only(['module_id', 'title', 'type_of_assessment', 'uploader_id']));

    if ($request->has('is_topic')) {
      if ($request->is_topic) {
          $course_assessment->assessment_has_topics()->attach($request->topic_or_sub_topic_id);
      } else {
          $course_assessment->assessment_has_sub_topics()->attach($request->topic_or_sub_topic_id);
      }
    } else {
      
      $module = Module::where('id', $request->module_id)->withCount('module_content')->first();

      if($module) {
        $module->module_content()->create([
          'module_id' => $module->id,
          'contentable_id' => $course_assessment->id,
          'contentable_type' => 'App\\Models\\Assessment',
          'sort_no' => $module->module_content_count + 1
        ]);
      }

    }

    $course->touch();

    return $this->sendResponse($course_assessment, 'You have successfully added a new assessment.', 201);
  }

  public function show($id)
  {
    //
  }

  public function edit($id)
  {
    //
  }

  public function update(AssessmentRequest $request, $id)
  {
    $assessment = Assessment::find($id);

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $assessment->update($request->safe()->except(['course_id', 'due_date']) + ['due_date' => $request->due_date]);

    //update active status of assessments for every instructor update on accepted_score

    if($assessment->wasChanged('accepted_score')) {
      if($assessment->accepted_score == 'highest'){
      
        $assessment_scores = AssessmentScore::where('assessment_id', $id)->latest()->get();

        $assessment_scores->groupBy('user_id')->each(function ($scores) {

          // set current active to 0
          $current_active = $scores->where('active', 1)->first();
          $current_active->active = 0; 
          $current_active->save();
          
          // update active assessment answers based on highest score
          $highest_score = $scores->sortByDesc('score')->first();
          $highest_score->active = 1;
          $highest_score->save();
        });
  
      }
      
      if (!$assessment->accepted_score || $assessment->accepted_score == 'latest') {
  
        $assessment_scores = AssessmentScore::where('assessment_id', $id)->get();

        $assessment_scores->groupBy('user_id')->each(function ($scores) {

          // set current active to 0
          $current_active = $scores->where('active', 1)->first();

          if($current_active) {
            $current_active->active = 0; 
            $current_active->save();
          }
          
          // update active assessment answers based on latest score
          $latest_score = $scores->sortByDesc('created_at')->first();
          if($latest_score) {
            $latest_score->active = 1;
            $latest_score->save();
          }

        });
  
      }
    }

    return $this->sendResponse($assessment, 'You have successfully updated an assessment.', 200);
  }

  public function destroy($id)
  {
    $assessment = Assessment::find($id);

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $assessment->assessment_questions()->delete();
    $assessment->assessment_scores()->delete();
    $assessment->question_answers()->delete();
    $assessment->shared_assessments()->delete();
    $assessment->assessment_has_topics()->detach();
    $assessment->assessment_has_sub_topics()->detach();
    $assessment->assessment_has_users()->detach();
    $assessment->student_module_progress()->delete();

    $assessment->module_content()->delete(); 
    
    $assessment->delete();

    return $this->sendResponse([], 'Assessment has been deleted.', 200);
  }

  public function showAssessmentQuestions($assessment_id)
  {
    $assessment = Assessment::find($assessment_id);

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $assessment_questions =  $assessment->assessment_questions()->with('assessment_answer')->get();

    return $this->sendResponse($assessment_questions, 'List of all questions under this assessment.', 200);
  }

  public function tagUserAccess($assessment_id)
  {
    $assessment = Assessment::find($assessment_id);

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $assessment->assessment_has_users()->sync(request('user_id'));

    return $this->sendResponse($assessment->assessment_has_users()->get(), 'Users access has been synced.', 200);
  }

  public function getTagUserAccess($assessment_id)
  {
    $users = [];
    $assessment = Assessment::find($assessment_id);

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $data = $assessment->assessment_has_users()->get();

    foreach ($data as $key => $value) {
      $users[$key] = Enrollment::where('user_id', $value->id)->with('class_category')->first();
    }
    return $this->sendResponse($users, 'Here are synced users', 200);
  }

  public function showClassHasUsers($course_id, $assessment_id)
  {
    $course = Course::find($course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $enrollments = $course->with([
      'enrollments' => function ($enrollments) use ($assessment_id) {
        return $enrollments->with([
          'user' => function ($user) use ($assessment_id) {
            return $user->withCount([
              'user_has_assessments' => function ($assessments) use ($assessment_id) {
                return $assessments->where('id', $assessment_id);
              }
            ]);
          }
        ]);
      }
    ])->where('id', $course_id)->get();

    return $this->sendResponse($enrollments, 'List of all classes with users enrolled', 200);
  }

  public function duplicateAssessment($assessment_id)
  {
    $assessment = Assessment::find($assessment_id);

    $topic = $assessment->assessment_has_topics->first();

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $assessment_count = Assessment::where('title', 'LIKE', '%' . $assessment->title . '%')->count();

    $new_assessment = $assessment->replicate()->fill([
      'title' => $assessment->title . '.copy' . ($assessment_count + 1)
    ]);

    $new_assessment->save();

    if($topic) {
      $new_assessment->assessment_has_topics()->attach($topic->id);
    }

    foreach ($assessment->assessment_questions as $key => $assessment_question) {
      $new_question = $new_assessment->assessment_questions()->create($assessment_question->toArray());
      $assessment_answer = AssessmentAnswer::where('assessment_question_id', $assessment_question->id)->first();
      $assessment_answer->replicate(['assessment_question_id'])
        ->fill([
          'assessment_question_id' => $new_question->id,
          'correct_answer' => $assessment_answer->correct_answer,
          'choices' => $assessment_answer->choices
        ])
        ->save();
    }

    return $this->sendResponse($new_assessment->id, 'This assessment has been duplicated.', 201);
  }

  public function moveAssessment(Request $request, $assessment_id)
  {
    $request->validate([
      'course_id' => 'required|exists:courses,id',
      'module_id' => 'sometimes|exists:modules,id',
      'is_topic' => 'sometimes|boolean',
      'topic_or_sub_topic_id' => 'sometimes',
    ]);

    $assessment = Assessment::find($assessment_id);

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $assessment->update(['course_id' => $request->course_id]);

    if($request->has('is_topic')) {
      if (!$request->is_topic) {
        $sub_topic = SubTopic::find($request->topic_or_sub_topic_id);
        $topic = Topic::find($sub_topic->topic_id);
        $assessment->update(['module_id' => $topic->module_id]);
      } else {
        $assessment->update(['module_id' => $request->module_id]);
      }
      $assessment->assessment_has_sub_topics()->detach();
      $assessment->assessment_has_topics()->detach();
  
      if (!$request->is_topic) {
        $assessment->assessment_has_sub_topics()->attach($request->topic_or_sub_topic_id);
      } else {
        $assessment->assessment_has_topics()->attach($request->topic_or_sub_topic_id);
      }
    } else {
      $module = Module::findOrFail($request->module_id);

      $module->module_content()->create([
        'module_id' => $module->id,
        'contentable_id' => $assessment->id,
        'contentable_type' => 'App\\Models\\Assessment',
        'sort_no' => $module->module_content_count + 1
      ]);

      $assessment->assessment_has_sub_topics()->detach();
      $assessment->assessment_has_topics()->detach();

    }


    return $this->sendResponse($assessment, 'This assessment has been moved.', 200);
  }

  public function shareAssessment(Request $request, $assessment_id)
  {
    $request->validate([
      'shared_to_user_id' => 'required|exists:users,id',
    ]);

    $assessment = Assessment::find($assessment_id);

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $shared_assessment_exist = ShareAssessment::where('assessment_id', $assessment_id)
      ->where('shared_by_user_id', $this->authUser()->id)
      ->where('shared_to_user_id', $request->shared_to_user_id)
      ->first();

    if ($shared_assessment_exist) {
      return $this->sendResponse([], 'You already shared the assessment to this user.', 200);
    }

    ShareAssessment::create([
      'assessment_id' => $assessment_id,
      'shared_by_user_id' => $this->authUser()->id,
      'shared_to_user_id' => $request->shared_to_user_id
    ]);

    $user = User::where('id', $request->shared_to_user_id)->firstOrFail();

    $data = [
      'name' => $this->authUser()->full_name,
      'photo' => $this->authUser()->image->url ?? null,
      'assessment' => $assessment->title,
      'assessment_uuid' => $assessment->uuid
    ];

    Notification::send($user, new ShareAssessmentNotification($data));

    return $this->sendResponse([], 'You successfully shared an assessment.', 201);
  }

  public function showSharedWithMeAssessments(Request $request)
  {
    $shared_assessments = ShareAssessment::has('assessment')
      ->with(['assessment', 'shared_by_user'])
      ->when(request('search'), function ($query) {
          $query->whereHas('assessment', function ($innerQuery) {
            $innerQuery->where('title', 'LIKE', '%' . request('search') . '%');
          });
        })
      ->when(!empty(request('date_to')) || !empty(request('date_from')), function ($query) {
        FilterHelper::queryCreatedAt($query, request('date_from'), request('date_to'));
      })
      ->where('shared_to_user_id', $this->authUser()->id)
      ->where('status', 'pending')
      ->paginate($request->paginate);

    return $this->sendResponse($shared_assessments, 'List of assessments shared with me.', 200);
  }

  public function showSharedToOtherAssessments(Request $request)
  {
    $shared_assessments = Assessment::whereHas('shared_assessments', function (Builder $query) {
        $query->where('shared_by_user_id', $this->authUser()->id)
        ->when(!empty(request('date_to')) || !empty(request('date_from')), function ($query) {
          FilterHelper::queryCreatedAt($query, request('date_from'), request('date_to'));
        });
      })
      ->when(request('search'), function($query){
        $query->where('title', 'LIKE', '%' . request('search') . '%');
      })
      ->with([
        'shared_assessments' => function ($shared_assessments) {
          $shared_assessments
            ->with('shared_to_user')
            ->where('shared_by_user_id', $this->authUser()->id);
        }
      ])
      ->where('uploader_id', $this->authUser()->id)
      ->paginate($request->paginate);

    return $this->sendResponse($shared_assessments, 'List of assessment you shared to others.', 200);
  }

  public function showShareToInstructors()
  {
    $instructors = User::select(['id', 'role', 'first_name', 'last_name', 'suffix', 'email'])->whereIn('role', [User::ROLE_INSTRUCTOR, User::ROLE_ADMIN])->get()->except($this->authUser()->id);

    return $this->sendResponse($instructors, 'List of all instructors to share assessment.', 200);
  }

  public function moveSharedWithMeAssessment(Request $request)
  {
    // $request->validate([
    //   'assessment_id' => 'required|exists:assessments,id',
    //   'course_id' => 'required|exists:courses,id',
    //   'is_topic' => 'required|boolean',
    //   'topic_or_sub_topic_id' => 'required',
    // ]);

    // $assessment = Assessment::find($request->assessment_id);

    // if (!$assessment) {
    //   return $this->sendResponse([], 'No assessment found.', 404);
    // }

    //$assessment->update(['course_id' => $request->course_id]);

    // if (!$request->is_topic) {
    //   $sub_topic = SubTopic::find($request->topic_or_sub_topic_id);
    //   $topic = Topic::find($sub_topic->topic_id);
    //   $assessment->update(['module_id' => $topic->module_id]);
    // } else {
    //   $assessment->update(['module_id' => $request->module_id]);
    // }
    // $assessment->assessment_has_sub_topics()->detach();
    // $assessment->assessment_has_topics()->detach();

    // if (!$request->is_topic) {
    //   // $sub_topic = SubTopic::find($request->topic_or_sub_topic_id);
    //   // $sub_topic->topic_has_assessments()->detach($assessment->id);
    //   $assessment->assessment_has_sub_topics()->attach($request->topic_or_sub_topic_id);
    // } else {
    //   // $topic = Topic::find($request->topic_or_sub_topic_id);
    //   // $topic->topic_has_assessments()->detach($assessment->id);
    //   $assessment->assessment_has_topics()->attach($request->topic_or_sub_topic_id);
    // }

    // return $this->sendResponse($assessment, 'This assessment has been moved.', 200);

    $request->validate([
      'assessment_id' => 'required|exists:assessments,id',
      'course_id' => 'required|exists:courses,id',
      'is_topic' => 'required|boolean',
      'topic_or_sub_topic_id' => 'required',
    ]);

    $assessment = Assessment::find($request->assessment_id);

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $shared_assessment = ShareAssessment::where('assessment_id', $request->assessment_id)->first();

    if($shared_assessment && $shared_assessment->status === 'pending') {
      $new_assessment = $assessment->replicate([
        'uploader_id'
      ])
        ->fill([
          'title' => $request->title,
          'course_id' => $request->course_id,
          'uploader_id' => $this->authUser()->id,
          'course_id' => $request->course_id
        ]);
  
      if (!$request->is_topic) {
        $sub_topic = SubTopic::find($request->topic_or_sub_topic_id);
        $topic = Topic::find($sub_topic->topic_id);
        $new_assessment->update(['module_id' => $topic->module_id]);
      } else {
        $new_assessment->update(['module_id' => $request->module_id]);
      }
  
      $new_assessment->save();
  
      if (!$request->is_topic) {
        //// $sub_topic = SubTopic::find($request->topic_or_sub_topic_id);
        //// $sub_topic->topic_has_assessments()->detach($assessment->id);
        $new_assessment->assessment_has_sub_topics()->attach($request->topic_or_sub_topic_id);
      } else {
        //// $topic = Topic::find($request->topic_or_sub_topic_id);
        //// $topic->topic_has_assessments()->detach($assessment->id);
        $new_assessment->assessment_has_topics()->attach($request->topic_or_sub_topic_id);
      }
  
      foreach ($assessment->assessment_questions as $key => $assessment_question) {
        $new_question = $new_assessment->assessment_questions()->create($assessment_question->toArray());
        $assessment_answer = AssessmentAnswer::where('assessment_question_id', $assessment_question->id)->first();
        $assessment_answer->replicate(['assessment_question_id'])
          ->fill([
            'assessment_question_id' => $new_question->id,
            'correct_answer' => $assessment_answer->correct_answer,
            'choices' => $assessment_answer->choices
          ])
          ->save();
      }

      $shared_assessment->update(['status' => 'accepted']);

      return $this->sendResponse($new_assessment->id, 'This assessment has been moved.', 201);
    } else {
      $assessment->update(['course_id' => $request->course_id]);

      if (!$request->is_topic) {
        $sub_topic = SubTopic::find($request->topic_or_sub_topic_id);
        $topic = Topic::find($sub_topic->topic_id);
        $assessment->update(['module_id' => $topic->module_id]);
      } else {
        $assessment->update(['module_id' => $request->module_id]);
      }
      $assessment->assessment_has_sub_topics()->detach();
      $assessment->assessment_has_topics()->detach();

      if (!$request->is_topic) {
        // $sub_topic = SubTopic::find($request->topic_or_sub_topic_id);
        // $sub_topic->topic_has_assessments()->detach($assessment->id);
        $assessment->assessment_has_sub_topics()->attach($request->topic_or_sub_topic_id);
      } else {
        // $topic = Topic::find($request->topic_or_sub_topic_id);
        // $topic->topic_has_assessments()->detach($assessment->id);
        $assessment->assessment_has_topics()->attach($request->topic_or_sub_topic_id);
      }

      return $this->sendResponse($assessment, 'This assessment has been moved.', 200);
    }
    
      

    

    // $request->validate([
    //   'assessment_id' => 'required|exists:assessments,id',
    //   'course_id' => 'required|exists:courses,id',
    //   'title' => 'required|max:255',
    // ]);

    // $assessment = Assessment::find($request->assessment_id);

    // if (!$assessment) {
    //   return $this->sendResponse([], 'No assessment found.', 404);
    // }

    // $new_assessment = $assessment->replicate([
    //   'uploader_id'
    // ])
    //   ->fill([
    //     'title' => $request->title,
    //     'course_id' => $request->course_id,
    //     'uploader_id' => $this->authUser()->id
    //   ]);

    // $new_assessment->save();

    // foreach ($assessment->assessment_questions as $key => $assessment_question) {
    //   $new_question = $new_assessment->assessment_questions()->create($assessment_question->toArray());
    //   $assessment_answer = AssessmentAnswer::where('assessment_question_id', $assessment_question->id)->first();
    //   $assessment_answer->replicate(['assessment_question_id'])
    //     ->fill([
    //       'assessment_question_id' => $new_question->id,
    //       'correct_answer' => $assessment_answer->correct_answer,
    //       'choices' => $assessment_answer->choices
    //     ])
    //     ->save();
    // }

    // $shared_assessment = ShareAssessment::where('assessment_id', $request->assessment_id)
    //   ->where('shared_to_user_id', $this->authUser()->id)
    //   ->update(['status' => 'accepted']);

    // return $this->sendResponse($new_assessment->id, 'This assessment has been moved.', 201);
  }

  public function getAssessment($assessment_id){
    $assessment = Assessment::where('id', $assessment_id)->with(['assessment_questions' => function($assessment_questions){
      $assessment_questions->with(['question_answer', 'assessment_answer']);
    }])->first();

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    return $this->sendResponse($assessment, 'Assessment found!', 200);
  }

  public function assessmentQuestionAnswers(Request $request, $assessment_id, $assessment_question_id)
  {
    $assessment = Assessment::where('id', $assessment_id)->first();

    if (!$assessment) {
      return $this->sendResponse([], 'No assessment found.', 404);
    }

    $answers = AssessmentScore::where('assessment_id', $assessment_id)
      ->when(($request->submitted_from || $request->submitted_to), function ($query) use ($request) {
        FilterHelper::queryCreatedAt($query, $request->submitted_from, $request->submitted_to);
      })
      ->whereHas('user', function ($query) use ($assessment){
        FilterHelper::filterEnrolledUsers($query, $assessment->course_id);
      })
      ->where('active', true)
      ->get();
    $questionAnswers = [];
    foreach ($answers as $answer) {
      array_push($questionAnswers, $answer->question_answers($assessment_question_id));
    }
    
    return $this->sendResponse($questionAnswers, 'List of assessment respondents answer summary for question', 200);
  }

  public function assessmentResponsesListExport(Request $request) {
    
    $export = Exports::create([
        'user_id' => $this->authUser()->id,
        'type' => 'assessment',
        'status' => 'processing',
        'exported_at' => now(),
        'filename' => '',
        'path' => '',
        'url' => ''
    ]);

    ProcessAssessmentExport::dispatch(
        $export->id,
        $request->assessment_id,
        $request->submitted_from,
        $request->submitted_to,
        $request->search
    );

    return response()->json([
        'message' => 'Export has been queued.',
        'status' => 'processing',
    ]);

  }

  public function assessmentResponsesSummaryExport(Request $request)
  {
    $export = Exports::create([
        'user_id' => $this->authUser()->id,
        'type' => 'assessment',
        'status' => 'processing',
        'exported_at' => now(),
        'filename' => '',
        'path' => '',
        'url' => ''
    ]);

    ProcessAssessmentSummaryExport::dispatch(
        $export->id,
        $request->assessment_id,
        $request->submitted_from,
        $request->submitted_to,
    );

    return response()->json([
        'message' => 'Export has been queued.',
        'status' => 'processing',
    ]);

  }

  public function sortAssessmentQuestions(Request $request)
  {
    foreach ($request->id as $key => $assessment_question_id) {
      AssessmentQuestion::where('id', $assessment_question_id)->update(['sort_no' => $key]);
    }

    return $this->sendResponse([], 'You have successfully sort assessment questions.', 200);
      
  }
}
