<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => 'sometimes|exists:leave_types,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'reason' => 'sometimes|string|min:10',
            'destination' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ];
    }
}
