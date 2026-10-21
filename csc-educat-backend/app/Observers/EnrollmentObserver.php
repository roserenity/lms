<?php

namespace App\Observers;

use App\Services\Tenants\ScormCloudService;

use App\Models\Enrollment;
use App\Models\Scorm;
use App\Models\ScormRegistration;
use App\Models\Certificate;
use App\Models\GroupActivity;
use Log;

class EnrollmentObserver
{
    /**
     * Handle the Enrollment "created" event.
     *
     * @param  \App\Models\Enrollment  $enrollment
     * @return void
     */
    public function created(Enrollment $enrollment)
    {
        //
        
        $scorms = Scorm::where('course_id', $enrollment->course_id)->with('my_registration')->get();
    
        foreach($scorms as $scorm) {

            $registration_id = $scorm->scorm_cloud_course_id .'-' . $enrollment->user_id;
            
            $result1 = ScormCloudService::getRegistration($enrollment->user_id, $scorm->scorm_cloud_course_id);

            if($result1['result'] === 'success') {
                if (!$scorm->launch_link) {
                    ScormCloudService::createLaunchLink($enrollment->user_id, $scorm->id, $scorm->scorm_cloud_course_id, $registration_id);
                }
            } else {
                $createRegistrationResult = ScormCloudService::createRegistration($enrollment->user_id, $scorm->scorm_cloud_course_id);
                if ($createRegistrationResult['result'] === 'success') {
                    ScormCloudService::createLaunchLink($enrollment->user_id, $scorm->id, $scorm->scorm_cloud_course_id, $registration_id);
                }
            }
        }

        // $group_activities = GroupActivity::where('course_id', $enrollment->course_id)->get();
        
        // foreach($group_activities as $group_activity) {
        //     $group = $group_activity->group_activity_groups()
        //         ->withCount('group_activity_has_group_members')
        //         ->orderBy('group_activity_has_group_members_count')
        //         ->first();

        //     if ($group) {
        //         $group->group_activity_has_group_members()->attach($enrollment->user_id);
        //     }
        // }

    }

    /**
     * Handle the Enrollment "updated" event.
     *
     * @param  \App\Models\Enrollment  $enrollment
     * @return void
     */
    public function updated(Enrollment $enrollment)
    {
        //
        Log::info('enrollment updated: '.$enrollment);
    }

    /**
     * Handle the Enrollment "deleted" event.
     *
     * @param  \App\Models\Enrollment  $enrollment
     * @return void
     */
    public function deleted(Enrollment $enrollment)
    {
        //
        Log::info('enrollment deleted: '.$enrollment);

        Certificate::where('user_id', $enrollment->user_id)->where('course_id', $enrollment->course_id)->delete();

        $scorm_registration_count = ScormRegistration::where('user_id', $enrollment->user_id)->count();

        if($scorm_registration_count > 0) {
            $scorms = Scorm::where('course_id', $enrollment->course_id)->get();

            foreach($scorms as $scorm) {
                $scorm_registrations = ScormRegistration::where('user_id', $enrollment->user_id)->where('scorm_id', $scorm->id)->get();
                foreach($scorm_registrations as $scorm_registration) {
                    ScormCloudService::deleteRegistrationTags($scorm_registration->registration_id);
                }
            }
        }
    }

    /**
     * Handle the Enrollment "restored" event.
     *
     * @param  \App\Models\Enrollment  $enrollment
     * @return void
     */
    public function restored(Enrollment $enrollment)
    {
        //
        Log::info('enrollment restored: '.$enrollment);
    }

    /**
     * Handle the Enrollment "force deleted" event.
     *
     * @param  \App\Models\Enrollment  $enrollment
     * @return void
     */
    public function forceDeleted(Enrollment $enrollment)
    {
        //
        Log::info('enrollment forceDeleted: '.$enrollment);
    }
}
