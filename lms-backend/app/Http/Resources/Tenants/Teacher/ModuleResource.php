<?php

namespace App\Http\Resources\Tenants\Teacher;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Tenants\Teacher\TopicResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'validity' => $this->validity,
            'available_from' => $this->available_from,
            'available_to' => $this->available_to,
            'sort_no' => $this->sort_no,
            'uploader_id' => $this->uploader_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'module_status' => $this->module_status,
            'prerequisites' => $this->prerequisites,
            // 'topics' => $this->topics
            'topics' => TopicResource::collection($this->topics),
            'module_evaluations' => $this->module_evaluations,
        ];
    }
}
