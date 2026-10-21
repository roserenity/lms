<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Tenants\ModuleRequest;
use App\Http\Requests\Tenants\UpdateModuleValidityRequest;
use App\Http\Requests\Tenants\ModuleSortRequest;
use App\Http\Requests\Tenants\ModulePrerequisiteRequest;
use App\Services\Tenants\ScormCloudService;

use App\Models\Course;
use App\Models\Module;
use App\Models\ModulePrerequisite;
use App\Models\Topic;
use App\Models\CustomModuleField;
use App\Models\ModuleContent;
use App\Models\ModuleEvaluation;
use Log;

class ModuleController extends Controller
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

  public function store(ModuleRequest $request)
  {
    $course = Course::find($request->course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $module = Module::create($request->validated());

    return $this->sendResponse($module, 'You have successfully added a new module.', 201);
  }

  public function show($id)
  {
    $module = Module::find($id);

    if (!$module) {
      return $this->sendResponse([], 'No module found.', 404);
    }

    $module_prerequisites = $module->prerequisites->pluck('prerequisite_module_id');
    $module_prerequisites->push($module->id);

    $modules = Module::with([
        'prerequisites',
        'topics' => fn ($topics) => $topics->with([
          'learning_material',
          'sub_topics' => fn ($sub_topics) => $sub_topics->with('learning_material')
        ])
      ])
      ->where('course_id', $module->course->id)
      ->whereNotIn('id', $module_prerequisites)
      ->get();

    return $this->sendResponse($modules, 'List of all available module prequisites for ' . $module->title, 201);
  }

  public function edit($id)
  {
    //
  }

  public function update(ModuleRequest $request, $id)
  {
    $module = Module::find($id);

    if (!$module) {
      return $this->sendResponse([], 'No module found.', 404);
    }

    $module->update($request->safe()->except('sort_no'));

    return $this->sendResponse($module, 'Module details has been updated.', 200);
  }

  public function destroy($id)
  {
    $module = Module::find($id);

    if (!$module) {
      return $this->sendResponse([], 'No module found.', 404);
    }

    //delete relationship
    ModulePrerequisite::where('prerequisite_module_id', $id)->delete();

    $module->delete();
    $module->assessments()->delete();
    $module->topics()->each(function($topic) {
      
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

      if($topic->scorm) {
        ScormCloudService::deleteCourse($topic->scorm->scorm_cloud_course_id);
      }
      $topic->scorm()->each(function($scorm) {
        $scorm->learner_registrations()->delete();
      });
      $topic->scorm()->delete();
      $topic->scorm_progress()->delete();
      
    });
    $module->topics()->delete();
    $module->prerequisites()->delete();
    $module->module_evaluations()->delete();

    return $this->sendResponse([], $module->title . ' has been deleted.', 200);
  }

  public function updateModuleValidity(UpdateModuleValidityRequest $request, $id)
  {

    $module = Module::find($id);

    if (!$module) {
      return $this->sendResponse([], 'No module found.', 404);
    }

    $module->update([
      'validity' => $request->validity,
      'available_from' => $request->available_from,
      'available_to' => $request->available_to,
    ]);

    return $this->sendResponse($module, 'Module validity has been updated.', 200);
  }

  public function sortModules(ModuleSortRequest $request)
  {
    foreach ($request->module_id as $key => $module_id) {
      $module = Module::where('id', $module_id)->update(['sort_no' => $key]);
    }

    $modules = Module::whereIn('id', $request->module_id)->get();

    return $this->sendResponse($modules, 'You have successfully sort modules.', 200);
  }

  public function sortModuleContents(Request $request)
  {
    foreach ($request->module_id as $key => $id) {
      ModuleContent::where('id', $id)->update(['sort_no' => $key]);
    }
    
    $course = Course::find($request->course_id);

    if (!$course) {
      return $this->sendResponse([], 'No course found.', 404);
    }

    $course->touch();

    return $this->sendResponse([], 'You have successfully sort module content.', 200);
  }

  public function syncModulePrerequisite(ModulePrerequisiteRequest $request)
  {
    $module = Module::find($request->module_id);

    if (!$module) {
      return $this->sendResponse([], 'No module found.', 404);
    }

    $module->prerequisites()->delete();

    foreach ($request->prerequisite_module_id as $key => $value) {
      $module->prerequisites()->create([
        'module_id' => $request->module_id,
        'prerequisite_module_id' => $request->prerequisite_module_id[$key],
        'uploader_id' => $this->authUser()->id
      ]);
    }

    $course = Course::find($request->course_id);

    $course->touch();

    return $this->sendResponse($module, 'You have successfully added a prerequisite to module.', 200);
  }

  public function getCustomModuleFields()
  {
      $custom_module_field = CustomModuleField::all();

      return $this->sendResponse($custom_module_field, 'List of all custom module setting.', 200);
  }

  public function getModuleEvaluations($module_id)
  {
    $module_evaluations = ModuleEvaluation::where('module_id', $module_id)->get();

    return $this->sendResponse($module_evaluations, 'List of all module evaluations under course' , 200);
  }

}
