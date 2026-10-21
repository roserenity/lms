<?php

namespace App\Http\Controllers\Api\V1\Tenants\Teacher;

use App\Models\Course;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\GroupActivityRequest;
use App\Models\GroupActivity;
use App\Models\GroupActivityGroup;
use App\Models\Module;
use App\Models\User;
use App\Services\Tenants\GoogleDriveService;
use App\Helpers\StandaloneConfig;
use Illuminate\Http\Request;

use Log;

class GroupActivityController extends Controller
{
    public function __construct()
    {
      $this->middleware('auth.instructor');
    }

    public function store(GroupActivityRequest $request) 
    {
      $course = Course::find($request->course_id);

      $group_activity = $course->group_activities()->create($request->safe()->except('topic_or_sub_topic_id', 'is_topic'));

      if ($request->has('is_topic')) {
        if ($request->is_topic) {
            $group_activity->group_activity_has_topics()->attach($request->topic_or_sub_topic_id);
        } else {
            $group_activity->group_activity_has_sub_topics()->attach($request->topic_or_sub_topic_id);
        }
      } else {
        
        $module = Module::where('id', $request->module_id)->withCount('module_content')->first();

        if($module) {
          $module->module_content()->create([
            'module_id' => $module->id,
            'contentable_id' => $group_activity->id,
            'contentable_type' => 'App\\Models\\GroupActivity',
            'sort_no' => $module->module_content_count + 1
          ]);
        }

      }

      $course->touch();
      
      return $this->sendResponse([], 'You have successfully added a new group activity.', 201);

    }

    public function show($id)
    {
      $group_activity = GroupActivity::with('group_activity_questions')->find($id);

      if (!$group_activity) {
          return $this->sendResponse([], 'No group activity found.', 404);
      }

      return $this->sendResponse($group_activity, 'Show group activity details', 200);
      
    }
    
    public function update(GroupActivityRequest $request, $id)
    {
        $group_activity = GroupActivity::find($id);

        if (!$group_activity) {
            return $this->sendResponse([], 'No group activity found.', 404);
        }

        $original_group_count = $group_activity->group_count;

        // Update main group activity excluding id and uuid
        $group_activity->update([
          'due_date' => $request->due_date,
          'group_activity_tool' => $request->group_activity_tool,
          'description' => $request->description,
          'title' => $request->title,
          'group_count' => $request->group_count,
        ]
        );

        // Update or create group activity questions
        if (!empty($request->group_activity_questions)) {
          foreach ($request->group_activity_questions as $group_activity_question) {
            if (!empty($group_activity_question['id'])) {
              $group_activity->group_activity_questions()->updateOrCreate(
                  [
                      'id' => $group_activity_question['id'],
                      'group_activity_id' => $group_activity->id,
                  ],
                  [
                      'question' => $group_activity_question['question']
                  ]
              );
            }
          }
        }

        // Handle group count changes
        if ($request->group_count != $original_group_count) {
          $new_count = $request->group_count ?? 0;
          $previous_count = $original_group_count ?? 0;

          if ($new_count < $previous_count) {
              $google_drive_service = app(GoogleDriveService::class);
              $groups = $group_activity->group_activity_groups;
              $groupsToDelete = $groups->take($previous_count - $new_count);

              foreach ($groupsToDelete as $group) {
                  if ($group->file_id) {
                      $google_drive_service->deleteFile($group->file_id);
                  }
                  $group->group_activity_has_group_members()->detach();
                  $group->delete();
              }
          }

          $current_group_count = $group_activity->group_activity_groups()->count();
          Log::info('Current group count: ' . $current_group_count);
          if ($new_count > $current_group_count) {
            for ($i = 1; $i <= $new_count; $i++) {
              $existing_group = $group_activity->group_activity_groups()->where('name', 'Group ' . $i)->first();
              
              // Only create the group if it does not already exist
              if (!$existing_group) {
                  $group_activity->group_activity_groups()->create([
                      'name' => 'Group ' . $i,
                      'group_activity_id' => $group_activity->id, // Ensure group is linked to activity
                  ]);
              }
            }
          }

          if ($previous_count == 0) {
            $enrollments = User::whereHas('enrollments', function ($query) use ($group_activity) {
                $query->where('course_id', $group_activity->course_id);
            })
            ->whereDoesntHave('group_activity_has_group_members', function ($subquery) use ($group_activity) {
                $subquery->where('group_activity_id', $group_activity->id);
            })
            ->get();

            $groups = $group_activity->group_activity_groups()->get();
            $groupCount = $groups->count();
            $index = 0;

            foreach ($enrollments as $user) {
                $groups[$index % $groupCount]->group_activity_has_group_members()->attach($user->id);
                $index++;
            }
          }
        }

        return $this->sendResponse([], 'You have successfully updated a group activity.', 200);
    }


    public function destroy($id)
    {
      $group_activity = GroupActivity::find($id);

      if (!$group_activity) {
          return $this->sendResponse([], 'No group activity found.', 404);
      }

      $group_activity->group_activity_groups()->delete();
      $group_activity->student_module_progress()->delete();
      $group_activity->group_activity_questions()->delete();
      $group_activity->group_activity_has_topics()->detach();
      $group_activity->group_activity_has_sub_topics()->detach();
      $group_activity->module_content()->delete();  
      $group_activity->delete();

      return $this->sendResponse([], 'You have successfully deleted a group activity.', 200);
    }

    public function getGroupActivityGroups($group_activity_id, Request $request)
    {
      $group_activity_groups = GroupActivityGroup::where('group_activity_id', $group_activity_id)->withCount('group_activity_has_group_members')
        ->when(!empty(request('search')), function ($query) {
          $query->where('name', 'LIKE', '%' . request('search') . '%');
        })
        ->orderBy('created_at', 'DESC')
        ->paginate($request->paginate);

      return $this->sendResponse($group_activity_groups, 'Show all groups under group activity', 200);
    }

    public function deleteGroupActivityGroup($group_activity_id, $group_id) 
    {

      $group_activity = GroupActivity::find($group_activity_id);

      if (!$group_activity) {
          return $this->sendResponse([], 'No group activity found.', 404);
      }

      $group_activity_group = GroupActivityGroup::find($group_id);

      if(!$group_activity_group) {
          return $this->sendResponse([], 'No group activity group found.', 404);
      }

      $group_activity_group->group_activity_has_group_members()->detach();

      if($group_activity_group->file_id)
      {
        
        $google_drive_service = app(GoogleDriveService::class);

        $google_drive_service->deleteFile($group_activity_group->file_id);

      }

      $group_activity_group->delete();
      $group_activity->group_count =  $group_activity->group_count - 1;
      $group_activity->save();

      return $this->sendResponse([], 'Successfully deleted group activity group', 200);
      
    }

    public function getGroupActivityGroupMembers($group_activity_id, $group_id, Request $request)
    {

      $group_activity = GroupActivity::find($group_activity_id);

      if (!$group_activity) {
          return $this->sendResponse([], 'No group activity found.', 404);
      }

      $group_activity_group = GroupActivityGroup::where('id', $group_id)->where('group_activity_id', $group_activity_id)->with(['group_activity_has_group_members' => fn($query) => $query->select(['id', 'first_name', 'last_name', 'suffix', 'email', 'role'])])->first();

      return $this->sendResponse($group_activity_group, 'Show all members under group activity group', 200);
    }

    public function getGroupActivityGroupNonMembers($group_activity_id)
    {

      $group_activity = GroupActivity::find($group_activity_id);

      if (!$group_activity) {
          return $this->sendResponse([], 'No group activity found.', 404);
      }

      $not_enrolled = User::whereHas('enrollments', function ($query) use ($group_activity) {
            $query->where('course_id', $group_activity->course_id);
        })
        ->whereDoesntHave('group_activity_has_group_members', function ($subquery) use ($group_activity_id) {
            $subquery->where('group_activity_id', $group_activity_id);
        })
        ->get();

      return $this->sendResponse($not_enrolled, "Show all enrolled that doesn't have a group", 200);
    }

    public function addGroupActivityGroupMembers($group_activity_id, $group_id, Request $request)
    {
      $group_activity = GroupActivity::find($group_activity_id);

      if (!$group_activity) {
          return $this->sendResponse([], 'No group activity found.', 404);
      }

      $group_activity_group = GroupActivityGroup::where('id', $group_id)->where('group_activity_id', $group_activity_id)->first();

      if (!$group_activity_group) {
        return $this->sendResponse([], 'No group activity group found.', 404);
      }

      foreach($request->user_id as $user_id)
      {
        $group_activity_group->group_activity_has_group_members()->attach($user_id);
      }

      return $this->sendResponse([], "Added group activity group members", 200);

    }

    public function removeGroupActivityGroupMembers($group_activity_id, $group_id, Request $request)
    {
      $group_activity = GroupActivity::find($group_activity_id);

      if (!$group_activity) {
          return $this->sendResponse([], 'No group activity found.', 404);
      }

      $group_activity_group = GroupActivityGroup::where('id', $group_id)->where('group_activity_id', $group_activity_id)->first();

      if (!$group_activity_group) {
        return $this->sendResponse([], 'No group activity group found.', 404);
      }

      foreach($request->user_id as $user_id)
      {
        $group_activity_group->group_activity_has_group_members()->detach($user_id);
      }

      return $this->sendResponse([], "Removed group activity group members", 200);

    }

    private function sanitizeFolderName($name) {
        // Replace invalid characters with underscores
        $name = preg_replace('/[\/\\\:\*\?"<>\|]/', '_', $name);
    
        // Remove control characters
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name);
    
        // Trim spaces
        $name = trim($name);
    
        // Optionally, lowercase and limit length
        $name = strtolower($name);
        $name = substr($name, 0, 255); // Max length for most filesystems
    
        // Avoid Windows reserved names
        $reserved = ['con', 'prn', 'aux', 'nul', 'com1', 'lpt1'];
        if (in_array($name, $reserved)) {
            $name = $name . '_folder';
        }
    
        return $name;
    }

    public function generateGroupActivityGroupFile($group_activity_id, $group_id)
    {
      $group_activity = GroupActivity::find($group_activity_id);

      if (!$group_activity) {
        return $this->sendResponse([], 'No group activity found.', 404);
      }

      $group_activity_group = GroupActivityGroup::where('id', $group_id)->where('group_activity_id', $group_activity_id)->first();

      if (!$group_activity_group) {
        return $this->sendResponse([], 'No group activity group found.', 404);
      }

      $google_drive_service = app(GoogleDriveService::class);

      if (!$google_drive_service->isConfigured()) {
        return $this->sendResponse([], 'Google Drive integration is not configured. Add a service account key to enable group activity files.', 503);
      }

      if($group_activity_group->file_id)
      {
        $google_drive_service->deleteFile($group_activity_group->file_id);
      }

      $course = Course::find($group_activity->course_id)->first();

      $folder_name = "{$course->id}"."{$course->course_code}"."{$course->title}";
      $course_name = $this->sanitizeFolderName($folder_name);
      $sub_folder_name = "{$group_activity->id}"."_{$group_activity->title}";

      Log::info($course_name);

      $shared_email = config('googleservice.google_drive_service_account');

      Log::info($shared_email);

      if($shared_email)
      {
        $folders = $google_drive_service->createFolderHierarchy(StandaloneConfig::tenantId(), $course_name, $sub_folder_name, $shared_email);
      } else {
        $folders = $google_drive_service->createFolderHierarchy(StandaloneConfig::tenantId(), $course_name, $sub_folder_name);
      }

      if($group_activity->group_activity_tool == GroupActivity::IS_GDOC)
      {
        $file = $google_drive_service->createGoogleDoc($folders['activity_folder_id'], "{$group_activity->id}_{$group_activity_group->name}", $group_activity_group->file_id);
      }

      if($group_activity->group_activity_tool == GroupActivity::IS_GSHEET)
      {
          $file = $google_drive_service->createGoogleSheet($folders['activity_folder_id'], "{$group_activity->id}_{$group_activity_group->name}", $group_activity_group->file_id);
      }

      if($group_activity->group_activity_tool == GroupActivity::IS_GSLIDE)
      {
          $file = $google_drive_service->createGoogleSlide($folders['activity_folder_id'], "{$group_activity->id}_{$group_activity_group->name}", $group_activity_group->file_id);
      }

      $group_activity_group->update(['file_url' => $file['link'], 'file_id' => $file['id']]);

      return $this->sendResponse([], "Generated Group Activity File", 200);

    }

}
