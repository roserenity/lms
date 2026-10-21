<?php

namespace App\Http\Resources\Tenants\User;

use Illuminate\Http\Resources\Json\JsonResource;

class EventZoomMeetingResource extends JsonResource
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
            'title' => $this->data['topic'],
            'start' => $this->data['start_date'],
            'start_date' => $this->data['start_date'],
            'time' => $this->data['start_time'],
            'join_url' => $this->data['join_url'],
            'color' => '#327DEE40',
            'textColor' => '#327DEE',
            'type' => 'meeting'
        ];
    }
}
