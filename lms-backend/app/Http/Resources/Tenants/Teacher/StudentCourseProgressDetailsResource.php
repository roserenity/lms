<?php

namespace App\Http\Resources\Tenants\Teacher;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Tenants\Teacher\ModuleResource;

use App\Models\StudentModuleProgress;
use App\Models\User;

class StudentCourseProgressDetailsResource extends JsonResource
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
            'description' => $this->description,
            'status' => $this->status,
            'uploader_id' => $this->uploader_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'assessments_count' => $this->assessments_count,
            'topics_count' => $this->topics_count,
            'sub_topics_count' => $this->sub_topics_count,
            'total_done_assessments' => self::totalDoneAssessments($request->user_uuid),
            'total_done_topics' => self::totalDoneTopics($request->user_uuid),
            'total_done_sub_topics' => self::totalDoneSubTopics($request->user_uuid),
            'course_card' => $this->course_card,
            'image' => $this->image,
            'modules' => ModuleResource::collection($this->modules)
        ];
    }

    public function totalDoneAssessments($user_uuid)
    {
        $user = User::where('uuid', $user_uuid)->first('id');

        $assessments = $this->assessments()->pluck('id');

        $total = StudentModuleProgress::whereIn('model_id', $assessments)
            ->where('model_type', 'App\\Models\\Assessment')
            ->where('user_id', $user->id)
            ->done()
            ->count();

        return $total;
    }

    public function totalDoneTopics($user_uuid)
    {
        $user = User::where('uuid', $user_uuid)->first('id');

        $topics = $this->topics()->pluck('id');

        $total = StudentModuleProgress::whereIn('model_id', $topics)
            ->where('model_type', 'App\\Models\\Topic')
            ->where('user_id', $user->id)
            ->done()
            ->count();

        return $total;
    }

    public function totalDoneSubTopics($user_uuid)
    {
        $user = User::where('uuid', $user_uuid)->first('id');

        $sub_topics = $this->sub_topics()->pluck('id');

        $total = StudentModuleProgress::whereIn('model_id', $sub_topics)
            ->where('model_type', 'App\\Models\\SubTopic')
            ->where('user_id', $user->id)
            ->done()
            ->count();

        return $total;
    }
}
