<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassScheduleRequest extends FormRequest
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
        return [
            'academic_year_id' => 'required|exists:academic_years,id',
            'education_id' => 'required|exists:educations,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'class_group_id' => 'required|exists:class_groups,id',
            'day' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'session' => 'required|in:pagi,sore,siang,malam',
            'status' => 'sometimes|in:active,inactive',
            'details' => 'required|array|min:1',
            'details.*.lesson_hour_id' => 'required|exists:lesson_hours,id',
            'details.*.teacher_id' => 'required|exists:staff,id',
            'details.*.study_id' => 'required|exists:studies,id',
        ];
    }

    public function messages()
    {
        return [
            'academic_year_id.required' => 'Tahun akademik wajib diisi',
            'academic_year_id.exists' => 'Tahun akademik tidak ditemukan',
            'education_id.required' => 'Program studi wajib diisi',
            'education_id.exists' => 'Program studi tidak ditemukan',
            'classroom_id.required' => 'Kelas wajib diisi',
            'classroom_id.exists' => 'Kelas tidak ditemukan',
            'class_group_id.required' => 'Rombel wajib diisi',
            'class_group_id.exists' => 'Rombel tidak ditemukan',
            'day.required' => 'Hari wajib diisi',
            'day.in' => 'Hari tidak valid',
            'session.required' => 'Sesi wajib diisi',
            'session.in' => 'Sesi tidak valid',
            'details.required' => 'Detail jadwal wajib diisi',
            'details.array' => 'Detail jadwal harus berupa array',
            'details.min' => 'Minimal harus ada 1 detail jadwal',
            'details.*.lesson_hour_id.required' => 'Jam pelajaran wajib diisi',
            'details.*.lesson_hour_id.exists' => 'Jam pelajaran tidak ditemukan',
            'details.*.teacher_id.required' => 'Guru wajib diisi',
            'details.*.teacher_id.exists' => 'Guru tidak ditemukan',
            'details.*.study_id.required' => 'Mata pelajaran wajib diisi',
            'details.*.study_id.exists' => 'Mata pelajaran tidak ditemukan',
        ];
    }
}
