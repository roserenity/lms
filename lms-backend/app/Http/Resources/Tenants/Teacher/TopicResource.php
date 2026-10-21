<?php

namespace App\Http\Resources\Tenants\Teacher;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Tenants\Teacher\SubTopicResource;

use App\Models\User;
use App\Models\StudentModuleProgress;

class TopicResource extends JsonResource
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
            'title' => $this->title,
            'course_id' => $this->course_id,
            'module_id' => $this->module_id,
            'sort_no' => $this->sort_no,
            'uploader_id' => $this->uploader_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'student_done_status' => self::studentDoneStatus($request->user_uuid),
            'topic_has_assessments' => $this->topic_has_assessments,
            'learning_material' => $this->learning_material,
            'sub_topics' => SubTopicResource::collection($this->sub_topics)
        ];
    }

    public function studentDoneStatus($user_uuid)
    {
        $user = User::where('uuid', $user_uuid)->first('id');

        $progress = StudentModuleProgress::where('model_id', $this->id)
            ->where('model_type', 'App\\Models\\Topic')
            ->where('user_id', $user->id)
            ->distinct('model_id')
            ->done()
            ->count();

        return $progress > 0 ? true : false;
    }
}
