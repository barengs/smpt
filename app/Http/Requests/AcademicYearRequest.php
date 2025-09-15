<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcademicYearRequest extends FormRequest
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
            'year' => 'required|string|size:9|unique:academic_years,year',
            'active' => 'nullable|boolean',
            'description' => 'nullable|string|max:255',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['year'] = 'sometimes|required|string|size:9|unique:academic_years,year,' . $this->route('academic_year');
            $rules['active'] = 'sometimes|nullable|boolean';
            $rules['description'] = 'sometimes|nullable|string|max:255';
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
            'year.required' => 'Tahun ajaran wajib diisi.',
            'year.string' => 'Tahun ajaran harus berupa teks.',
            'year.size' => 'Format tahun ajaran harus sesuai (contoh: 2023/2024).',
            'year.unique' => 'Tahun ajaran sudah ada.',
            'active.boolean' => 'Status aktif harus berupa nilai boolean.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal 255 karakter.',
        ];
    }
}
