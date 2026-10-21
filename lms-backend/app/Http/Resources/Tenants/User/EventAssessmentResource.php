<?php

namespace App\Http\Resources\Tenants\User;

use Illuminate\Http\Resources\Json\JsonResource;

class EventAssessmentResource extends JsonResource
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
            'title' => $this->title,
            'start' => json_decode($this->due_date, true)['start'],
            'end' => json_decode($this->due_date, true)['end'],
            'uuid' => $this->course->uuid,
            'color' => '#10B52A40',
            'textColor' => '#10B52A',
            'type' => 'assessment'
        ];
    }
}
