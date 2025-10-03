<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InternshipRequest extends FormRequest
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
            'student_id' => 'required|exists:students,id',
            'supervisor_id' => 'required|exists:internship_supervisors,id',
            'status' => 'sometimes|in:pending,approved,rejected',
            'file' => 'nullable|string|max:255',
            'long_term' => 'nullable|integer',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['academic_year_id'] = 'sometimes|required|exists:academic_years,id';
            $rules['student_id'] = 'sometimes|required|exists:students,id';
            $rules['supervisor_id'] = 'sometimes|required|exists:internship_supervisors,id';
            $rules['status'] = 'sometimes|in:pending,approved,rejected';
            $rules['file'] = 'sometimes|nullable|string|max:255';
            $rules['long_term'] = 'sometimes|nullable|integer';
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
            'academic_year_id.exists' => 'Tahun akademik tidak ditemukan.',
            'student_id.required' => 'Siswa wajib diisi.',
            'student_id.exists' => 'Siswa tidak ditemukan.',
            'supervisor_id.required' => 'Supervisor wajib diisi.',
            'supervisor_id.exists' => 'Supervisor tidak ditemukan.',
            'status.in' => 'Status harus salah satu dari: pending, approved, rejected.',
            'file.string' => 'File harus berupa teks.',
            'file.max' => 'File maksimal 255 karakter.',
            'long_term.integer' => 'Jangka waktu harus berupa angka.',
        ];
    }
}
