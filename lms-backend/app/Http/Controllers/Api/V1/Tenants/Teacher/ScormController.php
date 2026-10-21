<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Exports\Tenants\Teacher\ScormCourseLearnersList;
use App\Http\Resources\Tenants\Teacher\ScormRegistrationProgressResource;

use App\Helpers\Helper;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Models\Scorm;
use App\Models\ScormRegistration;
use App\Models\ScormRegistrationInteraction;
use App\Models\ScormRegistrationProgress;
use Illuminate\Http\Request;

use App\Services\Tenants\ScormCloudService;

use Log;

use Carbon\Carbon;

class ScormController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.instructor');
    }

    public function index(Request $request)
    {
        $scorm_activity = Scorm::all();
    }

    public function updateScormPreviewLink(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:scorms,id'
        ]);

        $scorm = Scorm::find($request->id);

        $result = ScormCloudService::createPreviewLink($scorm->scorm_cloud_course_id);
        
        if($result['result'] == 'success') {
            return $this->sendResponse($result['preview_link'], 'Updated SCORM course preview link', 200);
        } else {
            Log::info('Update Scorm Preview Link error'. $result['message']);
            return $this->sendResponse([], 'Failed to update SCORM course preview link.', 500);
        }
        
    }

    public function getScormUploadProgress(Request $request)
    {
        $request->validate([
            'jobId' => 'required|exists:scorms,job_id'
        ]);

        $scorm = Scorm::where('job_id', $request->jobId)->first();

        if (!$scorm) {
            return $this->sendResponse([], 'Scorm job not found.', 404);
        }

        $errors = [];

        $job_result = ScormCloudService::getCourseUploadStatus($request->jobId);

        if($job_result["job_status"] == 'COMPLETE') {
            $setCourseTitleResult = ScormCloudService::setCourseTitle($scorm->scorm_cloud_course_id, $scorm->title);

            if ($setCourseTitleResult['result'] !== 'success') {
                $errors[] = "Failed to set course title: " . json_encode($setCourseTitleResult['message']);
            }

            $setCourseConfigurationResult = ScormCloudService::setCourseConfiguration($scorm->scorm_cloud_course_id);
            if ($setCourseConfigurationResult['result'] !== 'success') {
                $errors[] = "Failed to set course configuration: " . json_encode($setCourseConfigurationResult['message']);
            }
      
            // Create a preview link
            $createPreviewLinkResult = ScormCloudService::createPreviewLink($scorm->scorm_cloud_course_id);
            if ($createPreviewLinkResult['result'] !== 'success') {
                $errors[] = "Failed to create preview link: " . json_encode($createPreviewLinkResult['message']);
            }

            $scorm->update([
                'status' => $job_result["job_status"],
                'data' => json_encode($job_result),
                'errors' => $errors
            ]);

            if (!empty($errors)) {
                return $this->sendResponse([], 'Something went wrong in updating course details.', 500);
            }
        } else {
            $scorm->update([
                'status' => $job_result["job_status"],
                'data' => json_encode($job_result),
                'errors' => $errors
            ]);
        }

        $scorm = $scorm->fresh();

        return $this->sendResponse($scorm, 'Scorm course job status', 200);
    }

    public function getScormCourseSummary(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:scorms,id'
        ]);

        $scorm = Scorm::find($request->id);

        $results = ScormCloudService::getWidgetReportageLink('summary/SummaryWidget', 'course', $scorm->scorm_cloud_course_id);

        if($results['result'] == 'success') {
            return $this->sendResponse($results['reportageLink'], 'List of all courses.', 200);
        } else {
            Log::info('Get scorm course summary error'. $results);
            return $this->sendResponse([], 'Failed to retrieve SCORM course summary.', 500);
        }
    }

    public function getScormCourseAllLearnersDetailsSummary(Request $request)
    {
        $request->validate([
          'id' => 'required|exists:scorms,id'
        ]);

        $scorm = Scorm::find($request->id);

        $results = ScormCloudService::getWidgetReportageLink('ViewAllDetailsWidget', 'course', $scorm->scorm_cloud_course_id);

        if($results['result'] == 'success') {
            return $this->sendResponse($results['reportageLink'], 'List of all courses.', 200);
        } else {
            Log::info('Get scorm course all learner details error'. $results);
            return $this->sendResponse([], 'Failed to retrieve SCORM course summary.', 500);
        }
    }

    public function getScormCourseLearners(Request $request)
    {
        $searchParts = explode(' ', $request->search);

        $request->validate([
          'id' => 'required|exists:scorms,id'
        ]);

        $scorm = Scorm::find($request->id);

        $scorm_registration = ScormRegistration::select(['id', 'scorm_id', 'user_id', 'scorm_registration_progress_id'])
            ->where('scorm_id', $request->id)->with(['scorm_progress', 'user' => function($query) {
                $query->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
            }])
            ->whereHas('user', function($query) use ($request, $searchParts, $scorm){
                FilterHelper::filterEnrolledUsers($query, $scorm->course_id);
                $query->when(!empty($request->search), function ($query) use ($searchParts) {
                    Helper::queryNameSearch($searchParts, $query);
                });
            })->paginate($request->paginate);
            
        return $this->sendResponse([ 'title' => $scorm->title, 'learners' => $scorm_registration ], 'List of all registrations.', 200);
    }

    public static function scormCourseLearnersExport(Request $request)
    {
        return new ScormCourseLearnersList($request->id, $request->search);

    }

    public function getScormCourseLearnerProgress(Request $request)
    {
        $request->validate([
          'scorm_id' => 'required|exists:scorms,id',
          'user_id' => 'required|exists:users,id',
        ]);

        $scorm_registration_progress = ScormRegistration::select(['id', 'scorm_id', 'user_id', 'scorm_registration_progress_id'])
            ->where('scorm_id', $request->scorm_id)
            ->where('user_id', $request->user_id)
            ->with(['scorm_progress', 'scorm', 'user' => function($query) {
                $query->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
            }])
            ->first();
                        
        return $this->sendResponse($scorm_registration_progress, 'Show learner progress', 200);
    }

    public function getScormCourseLearnersSubmissions(Request $request)
    {

        $searchParts = explode(' ', $request->search);
        
        $request->validate([
          'id' => 'required|exists:scorms,id'
        ]);

        $scorm_registration_progress = ScormRegistrationProgress::with(['user' => function($query) {
                $query->select(['id', 'first_name', 'last_name', 'suffix', 'email']);
            }], 'scorm_registration')
            ->whereHas('user', function($query) use ($request, $searchParts){
                $query->when(!empty($request->search), function ($query) use ($searchParts) {
                    Helper::queryNameSearch($searchParts, $query);
                });
            })
            ->whereHas('scorm_registration', function($query) use ($request){
                $query->whereHas('scorm', function($subquery) use ($request){
                    $subquery->where('id', $request->id);
                });
            })->paginate($request->paginate);

        ScormRegistrationProgressResource::collection($scorm_registration_progress);

        /* Log::info(json_encode($scorm_registration_progress_resource)); */
        /* $scorm_registration_progress->data = $scorm_registration_progress_resource; */
                        
        return $this->sendResponse($scorm_registration_progress, 'List of all registrations progress.', 200);
    }

    public function getScormCourseLearnerInteractions(Request $request) {

        $request->validate([
          'scorm_registration_id' => 'required|exists:scorm_registrations,id',
          'user_id' => 'required|exists:users,id',
        ]);

        $scorm_registration_interactions = ScormRegistrationInteraction::where('scorm_registration_id', $request->scorm_registration_id)->where('user_id', $request->user_id)->latest()->paginate($request->paginate); 

        return $this->sendResponse($scorm_registration_interactions, 'List of all registration interactions.', 200);

    }
    
}