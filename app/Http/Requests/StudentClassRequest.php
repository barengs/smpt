<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'academic_year_id' => 'required|exists:academic_years,id',
            'educational_institution_id' => 'required|exists:educational_institutions,id',
            'student_id' => 'required|exists:students,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'class_group_id' => 'required|exists:class_groups,id',
            'approval_status' => 'sometimes|in:diajukan,disetujui,ditolak',
            'approval_note' => 'nullable|string',
            'approved_by' => 'nullable|exists:users,id'
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['academic_year_id'] = 'sometimes|required|exists:academic_years,id';
            $rules['educational_institution_id'] = 'sometimes|required|exists:educational_institutions,id';
            $rules['student_id'] = 'sometimes|required|exists:students,id';
            $rules['classroom_id'] = 'sometimes|required|exists:classrooms,id';
            $rules['class_group_id'] = 'sometimes|required|exists:class_groups,id';
            $rules['approval_status'] = 'sometimes|in:diajukan,disetujui,ditolak';
            $rules['approval_note'] = 'sometimes|nullable|string';
            $rules['approved_by'] = 'sometimes|nullable|exists:users,id';
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun akademik wajib diisi.',
            'academic_year_id.exists' => 'Tahun akademik tidak valid.',
            'educational_institution_id.required' => 'Jenjang pendidikan wajib diisi.',
            'educational_institution_id.exists' => 'Jenjang pendidikan tidak valid.',
            'student_id.required' => 'Siswa wajib diisi.',
            'student_id.exists' => 'Siswa tidak valid.',
            'classroom_id.exists' => 'Kelas tidak valid.',
            'classroom_id.required' => 'Kelas wajib diisi.',
            'class_group_id.required' => 'Rombel wajib diisi.',
            'class_group_id.exists' => 'Rombel tidak valid.',
            'approval_status.in' => 'Status persetujuan tidak valid.',
            'approval_note.string' => 'Catatan persetujuan harus berupa teks.',
            'approved_by.exists' => 'Pengguna yang menyetujui tidak valid.',
        ];
    }
}
