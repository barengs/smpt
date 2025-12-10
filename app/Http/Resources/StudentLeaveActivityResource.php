<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentLeaveActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'activity_type' => $this->activity_type,
            'description' => $this->getActivityDescription(),
            'actor' => $this->actor ? [
                'id' => $this->actor->id,
                'name' => $this->actor->first_name . ' ' . $this->actor->last_name,
            ] : null,
            'actor_role' => $this->actor_role,
            'role_display' => $this->actor_role ? $this->getRoleDisplayName() : null,
            'metadata' => $this->metadata,
            'timestamp' => $this->created_at->format('Y-m-d H:i:s'),
            'ip_address' => $this->ip_address,
        ];
    }
}
