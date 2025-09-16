<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HostelRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:hostels,name',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'nullable|boolean',
            'program_id' => 'nullable|exists:programs,id',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'] = 'sometimes|required|string|max:255|unique:hostels,name,' . $this->route('hostel');
            $rules['description'] = 'sometimes|nullable|string';
            $rules['capacity'] = 'sometimes|nullable|integer|min:1';
            $rules['status'] = 'sometimes|nullable|boolean';
            $rules['program_id'] = 'sometimes|nullable|integer|exists:programs,id';
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
            'name.required' => 'Nama asrama wajib diisi.',
            'name.string' => 'Nama asrama harus berupa teks.',
            'name.max' => 'Nama asrama maksimal 255 karakter.',
            'name.unique' => 'Nama asrama sudah ada.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'capacity.integer' => 'Kapasitas harus berupa angka.',
            'capacity.min' => 'Kapasitas minimal 1.',
            'status.boolean' => 'Status harus berupa nilai boolean.',
        ];
    }
}
