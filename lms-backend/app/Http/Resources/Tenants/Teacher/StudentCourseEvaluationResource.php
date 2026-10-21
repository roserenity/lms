<?php

namespace App\Http\Resources\Tenants\Teacher;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentCourseEvaluationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this->student_course_evaluations->isNotEmpty()) {
            return [
                'id' => $this->id,
                'email' => $this->email,
                'student_course_evaluation_id' => $this->student_course_evaluations->first()->id,
                'uuid' => $this->uuid,
                'name' => $this->first_name . ' ' . $this->last_name . ' ' . $this->suffix,
                'status' => $this->student_course_evaluations->first()->is_checked,
                'attempts' => $this->student_course_evaluations->count(),
                'date_submitted' => $this->student_course_evaluations->first()->created_at,
            ];
        }
    }
}
