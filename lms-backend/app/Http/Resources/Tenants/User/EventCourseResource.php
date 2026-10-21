<?php

namespace App\Http\Resources\Tenants\User;

use Illuminate\Http\Resources\Json\JsonResource;

class EventCourseResource extends JsonResource
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
            'uuid' => $this->uuid,
            'title' => $this->title,
            'start' => $this->available_until['start_date'] ?? null,
            'end' => $this->available_until['end_date'] ?? null,
            'color' => '#EE8C3240',
            'textColor' => '#EE8C32',
            'type' => 'course'
        ];
    }
}
