<?php

namespace App\Http\Controllers\Api\V1\Tenants\User;

use App\Http\Controllers\Controller;

use App\Models\Course;
use App\Models\StudentCourseProgress;

use App\Helpers\Helper;

class StudentModuleProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.user');
    }

    public function getStudentCourseProgress($course_uuid)
    {
        
        $course = Course::where('uuid', $course_uuid)
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

        $user_id = $this->authUser()->id;
        
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
            
        return $this->sendResponse($progress, 'User progress', 200);
    }
    
}