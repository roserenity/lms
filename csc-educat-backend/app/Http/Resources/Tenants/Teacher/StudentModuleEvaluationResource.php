<?php

namespace App\Http\Resources\Tenants\Teacher;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentModuleEvaluationResource extends JsonResource
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
            'email' => $this->email,
            'student_module_evaluation_id' => $this->student_module_evaluations->first()->id,
            'uuid' => $this->uuid,
            'name' => $this->first_name . ' ' . $this->last_name . ' ' . $this->suffix,
            'status' => $this->student_module_evaluations->first()->is_checked,
            'attempts' => $this->student_module_evaluations->count(),
            'date_submitted' => $this->student_module_evaluations->first()->created_at,
        ];
    }
}
