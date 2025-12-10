<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentLeaveApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->approver_role,
            'role_display' => $this->getRoleDisplayName(),
            'approver' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->first_name . ' ' . $this->approver->last_name,
            ] : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'reviewed_at' => $this->reviewed_at?->format('Y-m-d H:i:s'),
            'approval_order' => $this->approval_order,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
