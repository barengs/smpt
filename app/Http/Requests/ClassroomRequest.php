<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:classrooms,name',
            'parent_id' => 'nullable|exists:classrooms,id',
            'description' => 'nullable|string',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'] = 'sometimes|required|string|max:255|unique:classrooms,name,' . $this->route('classroom');
            $rules['parent_id'] = 'sometimes|nullable|exists:classrooms,id';
            $rules['description'] = 'sometimes|nullable|string';
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
            'name.required' => 'Nama kelas wajib diisi.',
            'name.string' => 'Nama kelas harus berupa teks.',
            'name.max' => 'Nama kelas maksimal 255 karakter.',
            'name.unique' => 'Nama kelas sudah ada.',
            'parent_id.exists' => 'Kelas induk tidak valid.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ];
    }
}
