<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
            'destination' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'ID siswa harus diisi',
            'student_id.exists' => 'Siswa tidak ditemukan',
            'leave_type_id.required' => 'Jenis izin harus dipilih',
            'leave_type_id.exists' => 'Jenis izin tidak valid',
            'start_date.required' => 'Tanggal mulai harus diisi',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini',
            'end_date.required' => 'Tanggal selesai harus diisi',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai',
            'reason.required' => 'Alasan izin harus diisi',
            'reason.min' => 'Alasan izin minimal 10 karakter',
        ];
    }
}
