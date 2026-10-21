<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Course;
use App\Models\Module;
use App\Models\StudentModuleProgress;
use App\Models\SubTopic;
use App\Models\Topic;
use App\Models\StudentCompleteCourse;
use App\Models\AssessmentScore;

use Svg\Tag\Rect;

class ModuleController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth.user');
  }

  public function getModules($course_id)
  {
    $course = Course::find($course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $modules = $course->modules()->with([
      'module_content' => function ($query) {
        $query->with([
            'contentable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Topic::class => [
                        'scorm',
                        'topic_has_assessments',
                        'learning_material',
                        'sub_topics' => function ($query) {
                            $query->with([
                                'scorm',
                                'sub_topic_has_assessments',
                                'learning_material'
                            ])->orderBy('sort_no', 'ASC');
                        }
                    ],
                    \App\Models\Assessment::class => []
                ]);
            }
        ]);
      },
    ])->orderBy('sort_no', 'ASC')
    ->get();

    return $this->sendResponse($modules, 'List of all modules under ' . $course->title, 200);
  }

  public function getModule($module_id)
  {
    $module = Module::where('id', $module_id)->first();

    if (!$module) {
      return $this->sendResponse([], 'No module found.', 404);
    }

    $module = Module::where('id', $module_id)->with([
      'module_content' => function ($query) {
          $query->with([
              'contentable' => function ($morphTo) {
                  $morphTo->morphWith([
                      \App\Models\Topic::class => [
                        'topic_has_assessments' => function ($query) {
                            $query->with('student_assessment_progress');
                        },
                        'scorm' => function ($scorm) {
                            $scorm->with('my_registration');
                        },
                        'scorm_activities' => function ($scorm_activities) {
                            $scorm_activities->with('my_scorm_answers', function($query) {
                                $query->with('scorm_question');
                            });
                        },
                        'student_scorm_progress',
                        'student_topic_progress',
                        'sub_topics' => function ($sub_topics) {
                            $sub_topics->with([
                                'scorm' => function ($scorm) {
                                    $scorm->with('my_registration');
                                },
                                'sub_topic_has_assessments' => function ($query) {
                                    $query->with('student_assessment_progress');
                                },
                                'scorm_activities' => function ($scorm_activities) {
                                    $scorm_activities->with('my_scorm_answers', function($query) {
                                        $query->with('scorm_question');
                                    });
                                },
                                'student_scorm_progress',
                                'student_sub_topic_progress'
                            ]);
                        
                            $sub_topics->with([
                                'learning_material' => function ($query) {
                                    $query->select(['id', 'file_type', 'learnable_id', 'url', 'is_downloadable']);
                                    $query->getModel()->appendVimeoAttributes();
                                }
                            ]);
            
                            $sub_topics->orderBy('sort_no', 'ASC')
                                ->select([
                                    'id',
                                    'uuid',
                                    'course_id',
                                    'topic_id',
                                    'title'
                                ]);
            
                        },
                        'learning_material' => function ($query) {
                          $query->select(['id', 'file_type', 'learnable_id', 'url', 'is_downloadable'])->getModel()->appendVimeoAttributes();
                        }
        
                      ],
                      \App\Models\Assessment::class => [
                        'student_assessment_progress'
                      ]
                  ]);
              }
          ]);
      },
      'module_evaluations' => fn($module_evaluations) => $module_evaluations->withCount([
        'student_module_evaluations' => function ($student_module_evaluations) {
                return $student_module_evaluations->where('user_id', $this->authUser()->id);
            }, 
            'module_evaluation_questions'
        ])
        ->with([
            'student_module_evaluations' => function ($student_module_evaluations) {
                return $student_module_evaluations->where('user_id', $this->authUser()->id)->orderBy('id', 'DESC');
            }
        ])
    ])->first();

    if (!empty($module->module_content)) {
      foreach ($module->module_content as $module_content) {
          foreach ($module->module_content as $module_content) {
            if ($module_content->contentable_type === \App\Models\Topic::class) {
              if($module_content->contentable) {
                foreach ($module_content->contentable->topic_has_assessments as $assessment) {
                    $this->setAssessmentScore($assessment);
                }
    
                if ($module_content->contentable['sub_topics']) {
                    foreach ($module_content->contentable->sub_topics as $sub_topic) {
                        foreach ($sub_topic->sub_topic_has_assessments as $assessment) {
                            $this->setAssessmentScore($assessment);
                        }
                    }
                }
              }
            }
            if ($module_content->contentable_type === \App\Models\Assessment::class) {
              if($module_content->contentable) {
                $this->setAssessmentScore($module_content->contentable);
              }
            }
          }
      }
    }

    return $this->sendResponse($module, 'Module content', 200);

  }

  public function setAssessmentScore($assessment)
  {
      $assessment_scores = AssessmentScore::where('assessment_id', $assessment->id)
          ->where('user_id', $this->authUser()->id);
      
      $score = $assessment_scores->where('active', 1)->first();

      $assessment['assessment_score'] = [
          'score' =>  $score->score ?? null,
          'attempts' => $assessment_scores->count(),
          'created_at' => $score->created_at ?? null,
      ];
  }

  public function saveDoneContent(Request $request)
  {
    $request->validate([
      'course_id' => 'required|exists:courses,id',
      'model_uuid' => 'required|uuid',
      'model_type' => 'required|in:Topic,SubTopic,Assessment,CourseEvaluation,ModuleEvaluation,GroupActivity',
    ]);

    $model = 'App\\Models\\' . $request->model_type;

    if(in_array($request->model_type, ['Topic', 'SubTopic'])) {
      $data = $model::with('learning_material')->where('uuid', $request->model_uuid)->first();
    } else {
      $data = $model::where('uuid', $request->model_uuid)->first();
    }

    if (!$data) {
      return $this->sendResponse([], 'No data found.', 404);
    }

    $student_module_progress = StudentModuleProgress::where('model_id', $data->id)
      ->where('model_type', $model)
      ->where('course_id', $request->course_id)
      ->where('user_id', $this->authUser()->id)
      ->first();

    if(!$student_module_progress) {
      $student_module_progress = StudentModuleProgress::firstOrCreate([
        'model_id' => $data->id,
        'model_type' => $model,
        'course_id' => $request->course_id,
        'user_id' => $this->authUser()->id,
        'status' => StudentModuleProgress::STATUS_IN_PROGRESS,
        'time_spent' => 0
      ]);
    } else {
      if($request->status) {
        $student_module_progress->update([
          'status' => $request->status,
        ]);
      }
    }
    
    return $this->sendResponse($student_module_progress, 'Saved done content', 201);
  }

  public function next(Request $request)
  {
    $request->validate([
      'model_uuid' => 'required|uuid',
      'model_type' => 'required|in:Topic,SubTopic,Assessment,CourseEvaluation,ModuleEvaluation,GroupActivity',
      'save_progress' => 'required|boolean'
    ]);

    $model = 'App\\Models\\' . $request->model_type;

    $data = $model::with('learning_material')->where('uuid', $request->model_uuid)->first();

    if (!$data) {
      return $this->sendResponse([], 'No data found.', 404);
    }

    if ($request->model_type == 'Topic') {
      $current_topic = Topic::where('id', $data->id)->first();

      $next_topic = Topic::where('module_id', $current_topic->module_id)->where('sort_no', '>', $current_topic->sort_no)->first();

      $previous_topic = Topic::where('module_id', $current_topic->module_id)->where('sort_no', '<', $current_topic->sort_no)->latest()->first();

      $current_module = $current_topic->module()->first();
      $next_module = Module::where('course_id', $current_module->course_id)->where('sort_no', '>', $current_module->sort_no)->first();
      $previous_module = Module::where('course_id', $current_module->course_id)->where('sort_no', '<', $current_module->sort_no)->orderBy('sort_no', 'DESC')->first();

      if ($next_module) {
        $next_topic_of_next_module = Topic::where('module_id', $next_module->id)->where('sort_no', '>', $next_module->sort_no)->first(['uuid']);
      }

      if ($previous_module) {
        $previous_topic_of_previous_module = Topic::where('module_id', $previous_module->id)->where('sort_no', '>', $previous_module->sort_no)->latest()->first(['uuid']);
      }

      if (count($data->sub_topics) > 0) {
        $sub_topic = $data->sub_topics()->first(['uuid', 'topic_id']);

        $data['next'] = [
          'model_type' => 'sub_topic',
          'model_uuid' => $sub_topic->uuid ?? null
        ];
        $data['previous'] = [
          'model_type' => 'topic',
          'model_uuid' => $previous_topic->uuid ?? ($previous_topic_of_previous_module->uuid ?? null)
        ];
      } else {
        $data['next'] = [
          'model_type' => 'topic',
          'model_uuid' => $next_topic->uuid ?? ($next_topic_of_next_module->uuid ?? null)
        ];
        $data['previous'] = [
          'model_type' => 'topic',
          'model_uuid' => $previous_topic->uuid ?? ($previous_topic_of_previous_module->uuid ?? null)
        ];
        if (!empty($previous_topic->sub_topics)) {
          if (count($previous_topic->sub_topics) > 0) {
            $sub_topic = $previous_topic->sub_topics()->orderBy('sort_no', 'DESC')->first(['uuid']);

            $data['previous'] = [
              'model_type' => 'sub_topic',
              'model_uuid' => $sub_topic->uuid ?? null
            ];
          }
        }
      }
    } else if ($request->model_type == 'SubTopic') {
      $next_sub_topic_uuid = SubTopic::where('topic_id', $data->topic_id)->where('sort_no', '>', $data->sort_no)->first(['uuid']);
      $previous_sub_topic_uuid = SubTopic::where('topic_id', $data->topic_id)->where('sort_no', '<', $data->sort_no)->latest()->first(['uuid']);

      $topic = $data->topic()->first(['module_id', 'sort_no']);
      $topic_uuid = Topic::where('module_id', $topic->module_id)
        ->where('sort_no', '>', $topic->sort_no)
        ->first(['uuid', 'sort_no']);
      $previous_topic = Topic::where('module_id', $topic->module_id)
        ->where('sort_no', '<=', $topic->sort_no)
        ->latest()
        ->first(['uuid', 'sort_no']);

      $current_module = $topic->module()->first();
      $next_module = Module::where('course_id', $current_module->course_id)->where('sort_no', '>', $current_module->sort_no)->latest()->first();

      if ($next_module) {
        $next_topic = Topic::where('module_id', $next_module->id)->orderBy('sort_no', 'ASC')->first();
      }

      if ($next_sub_topic_uuid) {
        $data['next']  = [
          'model_type' => 'sub_topic',
          'model_uuid' => $next_sub_topic_uuid->uuid ?? null
        ];
        if ($previous_sub_topic_uuid) {
          $data['previous']  = [
            'model_type' => 'sub_topic',
            'model_uuid' => $previous_sub_topic_uuid->uuid ?? null
          ];
        } else {
          $data['previous']  = [
            'model_type' => 'topic',
            'model_uuid' => $previous_topic->uuid ?? null
          ];
        }
      } else {
        if ($topic_uuid) {
          $data['next']  = [
            'model_type' => 'topic',
            'model_uuid' => $topic_uuid->uuid ?? null
          ];
        } else {
          $data['next']  = [
            'model_type' => 'topic',
            'model_uuid' => $next_topic->uuid ?? null
          ];
        }
        if ($previous_sub_topic_uuid) {
          $data['previous']  = [
            'model_type' => 'sub_topic',
            'model_uuid' => $previous_sub_topic_uuid->uuid ?? null
          ];
        } else {
          $data['previous']  = [
            'model_type' => 'topic',
            'model_uuid' => $previous_topic->uuid ?? null
          ];
        }
      }
    }

    if ($request->save_progress === true) {
      StudentModuleProgress::firstOrCreate([
        'model_id' => $data->id,
        'model_type' => $model,
        'course_id' => $request->course_id,
        'user_id' => $this->authUser()->id,
      ]);
    }

    return $this->sendResponse($data, 'Next', 201);
  }

  public function post_progress(Request $request)
  {
    StudentModuleProgress::firstOrCreate([
      'model_id' => $request->model_id,
      'model_type' => $request->model,
      'course_id' => $request->course_id,
      'user_id' => $this->authUser()->id
    ]);
  }

  public function markCompleteCourse(Request $request)
  {
    $request->validate([
      'course_id' => 'required|exists:courses,id'
    ]);

    StudentCompleteCourse::firstOrCreate([
      'user_id' => $this->authUser()->id,
      'course_id' => $request->course_id
    ]);

    return $this->sendResponse([], 'Marked as complete course.', 201);
  }

  public function saveTimeSpent(Request $request)
  {
    $student_module_progress = StudentModuleProgress::updateOrCreate([
      'id' => $request->id,
      'model_id' => $request->model_id,
      'model_type' => $request->model_type,
      'user_id' => $this->authUser()->id,
      'course_id' => $request->course_id
    ], [
      'time_spent' => $request->time_spent
    ]);

    return $this->sendResponse($student_module_progress, 'Save time spent', 201);
  }

}
