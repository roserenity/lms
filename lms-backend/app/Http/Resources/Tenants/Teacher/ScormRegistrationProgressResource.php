<?php

namespace App\Http\Resources\Tenants\Teacher;

use Illuminate\Http\Resources\Json\JsonResource;

use Log; 

class ScormRegistrationProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this->id) {
            $json_activity_details = $this->activity_details ? json_decode($this->activity_details) : null;

            return [
                'id' => $this->id,
                'user' => $this->user,
                "completion_status" => $this->completion_status,
                "success_status" => $this->success_status,
                "total_seconds_tracked" => $this->total_seconds_tracked,
                "last_accessed_date" => $this->last_accessed_date,
                "first_accessed_date" => $this->first_accessed_date,
                'activity_id' => $json_activity_details->id ?? null,
                'activity_title' => $json_activity_details->title ?? null,
                'activity_attempts' => $json_activity_details->attempts ?? null,
                'activity_completion' => $json_activity_details->activityCompletion ?? null,
                'activity_success' => $json_activity_details->activitySuccess ?? null,
                'activity_time_tracked' => $json_activity_details->timeTracked ?? null,
                'children' => isset($json_activity_details->children) ? $this->transformChildren($json_activity_details->children) : [],
            ];
        }
    }

    private function transformChildren($children)
    {
        return collect($children)->map(function ($child) {
            Log::info(json_encode($child));
            return [
                'id' => $child->id ?? null,
                'title' => $child->title ?? null,
                'attempts' => $child->attempts ?? null,
                'activity_completion' => $child->activityCompletion ?? null,
                'activity_success' => $child->activitySuccess ?? null,
                'activity_time_tracked' => $child->timeTracked ?? null,
                'completion_amount' => $child->completionAmount ?? null,
                'answers' => $child->runtime->runtimeInteractions ?? [], // Fallback to empty array if not available
            ];            
        });
    }
}
