<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PresenceRequest extends FormRequest
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
            'student_id' => 'required|exists:students,id',
            'meeting_schedule_id' => 'required|exists:meeting_schedules,id',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'description' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'user_id' => 'required|exists:users,id',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['student_id'] = 'sometimes|required|exists:students,id';
            $rules['meeting_schedule_id'] = 'sometimes|required|exists:meeting_schedules,id';
            $rules['status'] = 'sometimes|required|in:hadir,izin,sakit,alpha';
            $rules['description'] = 'sometimes|nullable|string|max:255';
            $rules['date'] = 'sometimes|nullable|date';
            $rules['user_id'] = 'sometimes|required|exists:users,id';
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
            'student_id.required' => 'Siswa wajib diisi.',
            'student_id.exists' => 'Siswa tidak ditemukan.',
            'meeting_schedule_id.required' => 'Jadwal pertemuan wajib diisi.',
            'meeting_schedule_id.exists' => 'Jadwal pertemuan tidak ditemukan.',
            'status.required' => 'Status kehadiran wajib diisi.',
            'status.in' => 'Status kehadiran harus salah satu dari: hadir, izin, sakit, alpha.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
            'date.date' => 'Format tanggal tidak valid.',
            'user_id.required' => 'User wajib diisi.',
            'user_id.exists' => 'User tidak ditemukan.',
        ];
    }
}
