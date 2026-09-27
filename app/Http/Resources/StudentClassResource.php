<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentClassResource extends JsonResource
{
    protected $message;
    protected $statusCode;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $message
     * @param  mixed  $resource
     * @param  mixed  $statusCode
     * @return void
     */
    public function __construct($message, $resource, $statusCode)
    {
        parent::__construct($resource);
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->message,
            'status' => $this->statusCode,
            'data' => $this->resource ? [
                'id' => $this->id,
                'academic_year_id' => $this->academic_year_id,
                'educational_institution_id' => $this->educational_institution_id,
                'education_id' => $this->education_id,
                'student_id' => $this->student_id,
                'classroom_id' => $this->classroom_id,
                'class_id' => $this->class_id,
                'class_group_id' => $this->class_group_id,
                'approval_status' => $this->approval_status,
                'approval_note' => $this->approval_note,
                'approved_by' => $this->approved_by,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'academic_years' => $this->whenLoaded('academicYears'),
                'educations' => $this->whenLoaded('educations'),
                'students' => $this->whenLoaded('students'),
                'classrooms' => $this->whenLoaded('classrooms'),
                'class_group' => $this->whenLoaded('classGroup'),
            ] : null,
        ];
    }
}
