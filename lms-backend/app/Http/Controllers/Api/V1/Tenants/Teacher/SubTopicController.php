<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;

use App\Http\Requests\Tenants\UpdateSubTopicRequest;
use App\Http\Requests\Tenants\SubTopicRequest;
use App\Http\Requests\Tenants\SubTopicSortRequest;

use App\Services\Tenants\LearningMaterialService;
use App\Services\Tenants\ScormCloudService;

use App\Models\Topic;
use App\Models\SubTopic;

class SubTopicController extends Controller
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

  public function store(SubTopicRequest $request)
  {
    $topic = Topic::find($request->topic_id);

    if (!$topic) {
      return $this->sendResponse([], 'No topic found.', 404);
    }

    $sub_topic = SubTopic::create($request->validated());

    $response = LearningMaterialService::uploadFile([
      'course_id' => $topic->module->course_id,
      'model_id' => $sub_topic->id,
      'model_type' => 'App\Models\SubTopic',
      'type' => 'subtopic',
      'file' => $request->file,
      'youtube_link' => $request->youtube_link,
      's3_link' => $request->s3_link,
      'gdrive_link' => $request->gdrive_link,
      'scorm_file' => $request->scorm_file,
      'title' => $request->title,
      'is_downloadable' => $request->is_downloadable
    ]);

    if($response['result'] == 'error') {
      $sub_topic->delete();
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
    
    return $this->sendResponse($sub_topic, 'You have successfully added a new sub topic.', 201);
  }

  public function show($id)
  {
    //
  }

  public function edit($id)
  {
    //
  }

  public function update(UpdateSubTopicRequest $request, $id)
  {
    $topic = Topic::find($request->topic_id);

    if (!$topic) {
      return $this->sendResponse([], 'No topic found.', 404);
    }

    $sub_topic = SubTopic::find($id);

    if (!$sub_topic) {
      return $this->sendResponse([], 'No sub topic found.', 404);
    }

    $sub_topic->update($request->safe()->only('title'));

    if($request->has('is_downloadable')) { 
      $response = LearningMaterialService::uploadFile([
        'course_id' => $topic->module->course->id,
        'model_id' => $sub_topic->id,
        'model_type' => 'App\Models\SubTopic',
        'type' => 'subtopic',
        'file' => $request->file,
        'youtube_link' => $request->youtube_link,
        's3_link' => $request->s3_link,
        'gdrive_link' => $request->gdrive_link,
        'scorm_file' => $request->scorm_file,
        'title' => $request->title,
        'is_downloadable' => $request->is_downloadable
      ]);

      if($response['result'] == 'error') {
        $sub_topic->delete();
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

    return $this->sendResponse([], 'You have successfully updated a subtopic.', 201);
  }

  public function destroy($id)
  {
    $sub_topic = SubTopic::find($id);

    if (!$sub_topic) {
      return $this->sendResponse([], 'No sub topic found.', 404);
    }

    $sub_topic->delete();
    if($sub_topic->scorm) {
      ScormCloudService::deleteCourse($sub_topic->scorm->scorm_cloud_course_id);
    }
    $sub_topic->scorm()->each(function($scorm) {
      $scorm->learner_registrations()->delete();
    });
    $sub_topic->scorm()->delete();
    $sub_topic->scorm_progress()->delete();
    $sub_topic->scorm_activities()->delete();
    $sub_topic->learning_material()->delete();
    $sub_topic->student_sub_topic_progress()->delete();
    $sub_topic->sub_topic_has_assessments()->each(function($assessment) {
      $assessment->delete();
    });
    $sub_topic->student_module_progress()->delete();

    return $this->sendResponse([], $sub_topic->title . ' has been deleted.', 200);
  }

  public function sortSubTopics(SubTopicSortRequest $request)
  {
    foreach ($request->sub_topic_id as $key => $sub_topic_id) {
      $module = SubTopic::where('id', $sub_topic_id)->update(['sort_no' => $key]);
    }

    $sub_topics = SubTopic::whereIn('id', $request->sub_topic_id)->get();

    return $this->sendResponse($sub_topics, 'You have successfully sort sub topics.', 200);
  }
}
