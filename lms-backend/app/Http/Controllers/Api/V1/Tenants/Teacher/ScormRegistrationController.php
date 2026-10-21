<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Http\Controllers\Controller;

use App\Models\Scorm;
use App\Models\ScormRegistration;
use App\Models\Course;
use App\Models\StudentModuleProgress;
use App\Models\StudentCourseProgress;
use Illuminate\Http\Request;

use App\Helpers\Helper;
use App\Models\Certificate;
use App\Services\Tenants\ScormCloudService;

use Log;

class ScormRegistrationController extends Controller
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

  public function store(Request $request)
  {
    
  }

  public function show($id)
  {
    //
  }

  public function edit($id)
  {
    //
  }

  public function destroy($id)
  {
    $scorm_registration = ScormRegistration::where('id', $id)->with('scorm')->first();

    if (!$scorm_registration) {
      return $this->sendResponse([], 'No data found.', 404);
    }

    $result = ScormCloudService::deleteRegistration($scorm_registration->user_id, $scorm_registration->scorm_cloud_course_id);

    if($result['result'] == 'success') {

      // remove progress
      $student_module_progress = StudentModuleProgress::where('course_id', $scorm_registration->scorm->course_id)
          ->where('user_id', $scorm_registration->user_id)
          ->where('model_id', $scorm_registration->scorm->model_id)
          ->where('model_type', $scorm_registration->scorm->model_type)
          ->first();

      if($student_module_progress) {
        $student_module_progress->delete();
      }

      $course = Course::where('id', $scorm_registration->scorm->course_id)
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

      $user_id = $scorm_registration->user_id;
      
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
      ];

      $progress_percent = $course->type_of_learning === 'BLENDED' ? Helper::calculateLearnerProgressPercentBlended((object)$progress) : Helper::calculateLearnerProgressPercentElearning((object)$progress);

      StudentCourseProgress::updateOrCreate([
          'user_id' => $user_id,
          'course_id' => $course->id
      ], [
          ... $progress,
          'progress' => $progress_percent
      ]);

      $certificate = Certificate::where('user_id', $scorm_registration->user_id)->where('course_id', $scorm_registration->scorm->course_id)->first();

      if($certificate) {
        $certificate->delete();
      }

      return $this->sendResponse([], 'Scorm Registration has been deleted.', 200);
    } else {
      return $this->sendResponse([], 'Error in deleting scorm registration', 502);
    }

  }

  public function getScormCourseLearnerRegistrationSummary(Request $request)
  {
      $request->validate([
        'id' => 'required|exists:scorms,id',
        'user_id' => 'required|exists:users,id'
      ]);

      $scorm = Scorm::find($request->id);

      $results = ScormCloudService::getWidgetReportageLink('summary/SummaryWidget', 'learner', $scorm->scorm_cloud_course_id, $request->user_id);

      if($results['result'] == 'success') {
          return $this->sendResponse($results['reportageLink'], 'Course learner summary.', 200);
      } else {
          Log::info('Get scorm course learner summary error'. $results);
          return $this->sendResponse([], 'Failed to retrieve SCORM course learner summary.', 500);
      }
  }

}
