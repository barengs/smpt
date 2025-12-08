<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentLeaveReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_leave_id' => $this->student_leave_id,
            'report_date' => $this->report_date->format('Y-m-d'),
            'report_time' => $this->report_time,
            'report_notes' => $this->report_notes,
            'condition' => $this->condition,
            'is_late' => $this->is_late,
            'late_days' => $this->late_days,
            'reported_to' => $this->reportedToStaff ? [
                'id' => $this->reportedToStaff->id,
                'name' => $this->reportedToStaff->first_name . ' ' . $this->reportedToStaff->last_name,
            ] : null,
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
            'verified_by' => $this->verifiedByStaff ? [
                'id' => $this->verifiedByStaff->id,
                'name' => $this->verifiedByStaff->first_name . ' ' . $this->verifiedByStaff->last_name,
            ] : null,
            'penalties' => StudentLeavePenaltyResource::collection($this->whenLoaded('penalties')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
