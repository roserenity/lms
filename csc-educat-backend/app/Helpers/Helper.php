<?php

namespace App\Helpers;
use Illuminate\Support\Str;
use App\Helpers\StandaloneConfig;

use Log;
class Helper
{
  public static function stripSubDomainName($name)
  {
    return strtolower(str_replace(' ', '-',$name));
  }

  public static function generateTenantId($name)
  {
    return $name.'_'.Str::random(5);
  }

  public static function tenantName()
  {
    return explode('_', StandaloneConfig::tenantId())[0];
  }

  public static function bucketName()
  {
    return "bucket-".self::tenantName()."-".config('services.app_env');
  }

  public static function sort($data)
  {
    if (!isset($data)) return 0;
    return $data + 1;
  }

  public static function queryNameSearch($searchParts, $query)
  {
    foreach ($searchParts as $part) {
      $query
          //->where('first_name', 'LIKE', '%' . $part . '%')
          //->orWhere('middle_name', 'LIKE', '%' . $part . '%')
          //->orWhere('last_name', 'LIKE', '%' . $part . '%')
          ->WhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$part%"]);
    }

    return $query;
  }

  public static function queryNameEmailSearch($searchParts, $query)
  {
    foreach ($searchParts as $part) {
      $query->where(function ($query) use ($part) {
          $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$part%"])
                ->orWhere('email', 'LIKE', '%' . $part . '%');
      });
    }

    return $query;
  }
  
  public static function setLearnerCourseData($course, $user_id)
  {
    
    $course['total_done_topics'] = $course->getTotalDoneTopics($user_id);
    $course['total_done_sub_topics'] = $course->getTotalDoneSubTopics($user_id);
    $course['total_done_assessments'] = $course->getTotalDoneAssessments($user_id);
    $course['total_done_course_evaluations'] = $course->getTotalDoneCourseEvaluations($user_id);
    $course['total_done_module_evaluations'] = $course->getTotalDoneModuleEvaluations($user_id);
    $course['total_attended_zoom_meetings'] = $course->getTotalAttendedZoomMeetings($user_id);
    $course['total_done_group_activities'] = $course->getTotalDoneGroupActivities($user_id);
    $course['total_time_spent'] = $course->getTotalTimeSpent($course->id, $user_id);

    return $course;
  }

  public static function calculateLearnerProgressPercentElearning($progress)
  {
    $totalDone = $progress->total_done_assessments + 
        $progress->total_done_topics + 
        $progress->total_done_sub_topics + 
        $progress->total_done_course_evaluations + 
        $progress->total_done_module_evaluations +
        $progress->total_done_group_activities;

    Log::info(json_encode($progress));

    $totalCount = $progress->assessments_count + 
        $progress->topics_count + 
        $progress->sub_topics_count + 
        $progress->course_evaluations_count + 
        $progress->module_evaluations_count + 
        $progress->group_activities_count;

    if ($totalCount > 0) {
      return round(($totalDone / $totalCount) * 100);
    }

    return 0;
  }

  public static function calculateLearnerProgressPercentBlended($progress)
  {
    $totalDone = $progress->total_done_assessments + 
        $progress->total_done_topics + 
        $progress->total_done_sub_topics + 
        $progress->total_done_course_evaluations + 
        $progress->total_done_module_evaluations + 
        $progress->total_attended_zoom_meetings +
        $progress->total_done_group_activities;

    $totalCount = $progress->assessments_count + 
        $progress->topics_count + 
        $progress->sub_topics_count + 
        $progress->course_evaluations_count + 
        $progress->module_evaluations_count + 
        $progress->zoom_meetings_count + 
        $progress->group_activities_count;

    if ($totalCount > 0) {
      return round(($totalDone / $totalCount) * 100);
    }

    return 0;
  }

  public static function filterSpecifiedParams($queryParams, $specifiedParams)
  {
    $specifiedParamsFlipped = array_flip($specifiedParams);

    $filteredParams = array_diff_key($queryParams, $specifiedParamsFlipped);

    return array_keys($filteredParams);
  }

  public static function calculatePercentage(float $part, float $total): float
  {
      if ($total == 0.0) {
          return 0.0;
      }
      
      return ($part / $total) * 100.0;
  }


  public static function isValidDateString($value)
  {
      return $value && $value !== 'undefined-undefined' && strtotime($value) !== false;
  }

}
