<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EducationClassRequest extends FormRequest
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
            'code' => 'required|string|max:255|unique:education_classes,code',
            'name' => 'required|string|max:255',
            'education_ids' => 'nullable|array',
            'education_ids.*' => 'exists:educations,id',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['code'] = 'sometimes|required|string|max:255|unique:education_classes,code,' . $this->route('education_class');
            $rules['name'] = 'sometimes|required|string|max:255';
            $rules['education_ids'] = 'sometimes|nullable|array';
            $rules['education_ids.*'] = 'sometimes|exists:educations,id';
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
            'code.required' => 'Kode kelas pendidikan wajib diisi.',
            'code.string' => 'Kode kelas pendidikan harus berupa teks.',
            'code.max' => 'Kode kelas pendidikan maksimal 255 karakter.',
            'code.unique' => 'Kode kelas pendidikan sudah ada.',
            'name.required' => 'Nama kelas pendidikan wajib diisi.',
            'name.string' => 'Nama kelas pendidikan harus berupa teks.',
            'name.max' => 'Nama kelas pendidikan maksimal 255 karakter.',
            'education_ids.array' => 'Daftar pendidikan harus berupa array.',
            'education_ids.*.exists' => 'Salah satu pendidikan tidak valid.',
        ];
    }
}
