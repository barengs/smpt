<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentLeavePenaltyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'penalty_type' => $this->penalty_type,
            'description' => $this->description,
            'point_value' => $this->point_value,
            'sanction' => $this->sanction ? [
                'id' => $this->sanction->id,
                'name' => $this->sanction->name,
                'type' => $this->sanction->type,
            ] : null,
            'assigned_by' => $this->assignedByStaff ? [
                'id' => $this->assignedByStaff->id,
                'name' => $this->assignedByStaff->first_name . ' ' . $this->assignedByStaff->last_name,
            ] : null,
            'assigned_at' => $this->assigned_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
