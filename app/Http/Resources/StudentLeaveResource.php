<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentLeaveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'leave_number' => $this->leave_number,
            'created_by' => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->first_name . ' ' . $this->creator->last_name,
            ] : null,
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->first_name . ' ' . $this->student->last_name,
                'nis' => $this->student->nis,
            ],
            'leave_type' => [
                'id' => $this->leaveType->id,
                'name' => $this->leaveType->name,
            ],
            'academic_year' => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'duration_days' => $this->duration_days,
            'reason' => $this->reason,
            'destination' => $this->destination,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'status' => $this->status,
            'approved_by' => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->first_name . ' ' . $this->approver->last_name,
            ] : null,
            'approved_at' => $this->approved_at?->format('Y-m-d H:i:s'),
            'approval_notes' => $this->approval_notes,
            'expected_return_date' => $this->expected_return_date?->format('Y-m-d'),
            'actual_return_date' => $this->actual_return_date?->format('Y-m-d'),
            'has_penalty' => $this->has_penalty,
            'is_overdue' => $this->isOverdue(),
            'days_late' => $this->getDaysLate(),
            'requires_multi_approval' => $this->requires_multi_approval,
            'approval_count' => $this->approval_count,
            'required_approvals' => $this->required_approvals,
            'all_approved' => $this->all_approved,
            'approvals' => $this->whenLoaded('approvals', function () {
                return $this->approvals->map(function ($approval) {
                    return [
                        'id' => $approval->id,
                        'role' => $approval->approver_role,
                        'role_display' => $approval->getRoleDisplayName(),
                        'approver' => $approval->approver ? [
                            'id' => $approval->approver->id,
                            'name' => $approval->approver->first_name . ' ' . $approval->approver->last_name,
                        ] : null,
                        'status' => $approval->status,
                        'notes' => $approval->notes,
                        'reviewed_at' => $approval->reviewed_at?->format('Y-m-d H:i:s'),
                        'approval_order' => $approval->approval_order,
                    ];
                });
            }),
            'report' => $this->report ? new StudentLeaveReportResource($this->report) : null,
            'penalties' => StudentLeavePenaltyResource::collection($this->whenLoaded('penalties')),
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
