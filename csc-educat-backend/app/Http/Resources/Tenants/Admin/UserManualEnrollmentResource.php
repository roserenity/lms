<?php

namespace App\Http\Resources\Tenants\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class UserManualEnrollmentResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'courses_count' => $this->invoices()->count(),
            'status' => self::status(),
            'latest_date_enrolled' => $this->invoices()->orderBy('id', 'DESC')->first(),
        ];
    }

    public function status()
    {
        $invoices_pending = $this->invoices()->where('status', 2)->count();

        if ($invoices_pending === 0) {
            $invoices_disqualified = $this->invoices()->where('status', 3)->count();
            if ($this->invoices()->count() === $invoices_disqualified) {
                return 'disqualified';
            } else {
                return 'enrolled';
            }
        } else {
            return 'pending';
        }
    }
}
