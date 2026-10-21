<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

use App\Http\Requests\Tenants\UpdateTopicRequest;
use App\Http\Requests\Tenants\TopicRequest;
use App\Http\Requests\Tenants\TopicSortRequest;

use App\Services\Tenants\LearningMaterialService;
use App\Services\Tenants\ScormCloudService;

use App\Models\Module;
use App\Models\Topic;
use App\Models\SubTopic;

use Log;

class TopicController extends Controller
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

  public function store(TopicRequest $request)
  {
    $module = Module::where('id', $request->module_id)->withCount('module_content')->first();

    if (!$module) {
      return $this->sendResponse([], 'No module found.', 404);
    }

    $topic = Topic::create($request->validated());

    $response = LearningMaterialService::uploadFile([
      'course_id' => $request->course_id,
      'model_id' => $topic->id,
      'model_type' => 'App\Models\Topic',
      'type' => 'topic',
      'file' => $request->file,
      'youtube_link' => $request->youtube_link,
      's3_link' => $request->s3_link,
      'gdrive_link' => $request->gdrive_link,
      'scorm_file' => $request->scorm_file,
      'title' => $request->title,
      'is_downloadable' => $request->is_downloadable
    ]);

    if($response['result'] == 'error') {
      $topic->delete();
      return response()->json([
        'errors' => $response['errors']
      ], 422);
    }

    if ($request->file && $request->hasFile('file')) {
      if (config('app.env') != 'local' && $request->file->extension() == 'mp4') {
        LearningMaterialService::addPresetToVideo($response['url']);
        LearningMaterialService::addVideoToTenantFolderUri($response['url']);
      }
    }

    $module->module_content()->create([
      'module_id' => $module->id,
      'contentable_id' => $topic->id,
      'contentable_type' => 'App\\Models\\Topic',
      'sort_no' => $module->module_content_count + 1
    ]);

    return $this->sendResponse($topic, 'You have successfully added a new topic.', 201);
    
  }

  public function show($id)
  {
    //
  }

  public function edit($id)
  {
    //
  }

  public function update(UpdateTopicRequest $request, $id)
  {
    $module = Module::find($request->module_id);

    if (!$module) {
      return $this->sendResponse([], 'No module found.', 404);
    }

    $topic = Topic::find($id);

    if (!$topic) {
      return $this->sendResponse([], 'No topic found.', 404);
    }

    $topic->update($request->safe()->only('title'));

    if($request->has('is_downloadable')) {
      $response = LearningMaterialService::uploadFile([
        'course_id' => $module->course_id,
        'model_id' => $topic->id,
        'model_type' => 'App\Models\Topic',
        'type' => 'topic',
        'file' => $request->file,
        'youtube_link' => $request->youtube_link,
        's3_link' => $request->s3_link,
        'gdrive_link' => $request->gdrive_link,
        'scorm_file' => $request->scorm_file,
        'title' => $request->title,
        'is_downloadable' => $request->is_downloadable
      ]);

      if($response['result'] == 'error') {
        $topic->delete();
        return response()->json([
          'errors' => $response['errors']
        ], 422);
      }

      if ($request->file && $request->hasFile('file')) {
        if (config('app.env') != 'local' && $request->file->extension() == 'mp4') {
          LearningMaterialService::addPresetToVideo($response['url']);
          LearningMaterialService::addVideoToTenantFolderUri($response['url']);
        }
      }
    }

    return $this->sendResponse([], 'You have successfully updated a topic.', 201);
  }

  public function destroy($id)
  {
    $topic = Topic::find($id);

    if (!$topic) {
      return $this->sendResponse([], 'No topic found.', 404);
    }

    if($topic->scorm) {
      ScormCloudService::deleteCourse($topic->scorm->scorm_cloud_course_id);
    }
    $topic->scorm()->each(function($scorm) {
      $scorm->learner_registrations()->delete();
    });
    $topic->scorm()->delete();
    $topic->scorm_progress()->delete();
    $topic->scorm_activities()->delete();
    $topic->learning_material()->delete();
    $topic->sub_topics()->each(function($sub_topic) {
      if($sub_topic->scorm) {
        ScormCloudService::deleteCourse($sub_topic->scorm->scorm_cloud_course_id);
      }
      $sub_topic->scorm()->each(function($scorm) {
        $scorm->learner_registrations()->delete();
      });
      $sub_topic->scorm()->delete();
      $sub_topic->scorm_progress()->delete();
    });
    $topic->sub_topics()->delete();
    $topic->student_topic_progress()->delete();
    $topic->topic_has_assessments()->each(function($assessment) {
      $assessment->delete();
    });
    $topic->student_module_progress()->delete();
    
    $topic->module_content()->delete(); 

    $topic->delete();

    return $this->sendResponse([], $topic->title . ' has been deleted.', 200);
  }

  public function sortTopics(TopicSortRequest $request)
  {
    foreach ($request->topic_id as $key => $topic_id) {
      $module = Topic::where('id', $topic_id)->update(['sort_no' => $key]);
    }

    $topics = Topic::whereIn('id', $request->topic_id)->get();

    return $this->sendResponse($topics, 'You have successfully sort topics.', 200);
  }
}
