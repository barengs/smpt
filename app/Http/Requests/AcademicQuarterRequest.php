<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcademicQuarterRequest extends FormRequest
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
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'active' => 'nullable|boolean',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['academic_year_id'] = 'sometimes|required|exists:academic_years,id';
            $rules['name'] = 'sometimes|required|string|max:50';
            $rules['start_date'] = 'sometimes|required|date';
            $rules['end_date'] = 'sometimes|required|date|after_or_equal:start_date';
            $rules['active'] = 'sometimes|nullable|boolean';
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak valid.',
            'name.required' => 'Nama kuartal wajib diisi.',
            'name.string' => 'Nama kuartal harus berupa teks.',
            'name.max' => 'Nama kuartal maksimal 50 karakter.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.date' => 'Tanggal selesai harus berupa tanggal yang valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'active.boolean' => 'Status aktif harus berupa nilai boolean.',
        ];
    }
}
