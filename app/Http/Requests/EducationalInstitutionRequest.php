<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EducationalInstitutionRequest extends FormRequest
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
            'education_id' => 'required|exists:educations,id',
            'education_class_id' => 'required|exists:education_classes,id',
            'registration_number' => 'nullable|unique:educational_institutions,registration_number',
            'institution_name' => 'required|string|max:255',
            'institution_address' => 'nullable|string|max:255',
            'institution_phone' => 'nullable|string|max:20',
            'institution_email' => 'nullable|email|max:255',
            'institution_website' => 'nullable|url|max:255',
            'institution_logo' => 'nullable|string|max:255',
            'institution_banner' => 'nullable|string|max:255',
            'institution_status' => 'nullable|in:active,inactive',
            'institution_description' => 'required|string',
            'headmaster_id' => 'required|exists:staffs,id',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['registration_number'] = 'nullable|unique:educational_institutions,registration_number,' . $this->route('educational_institution');
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
            'education_id.required' => 'ID pendidikan wajib diisi.',
            'education_id.exists' => 'ID pendidikan tidak valid.',
            'education_class_id.required' => 'ID kelas pendidikan wajib diisi.',
            'education_class_id.exists' => 'ID kelas pendidikan tidak valid.',
            'registration_number.unique' => 'Nomor registrasi sudah digunakan.',
            'institution_name.required' => 'Nama institusi wajib diisi.',
            'institution_name.string' => 'Nama institusi harus berupa teks.',
            'institution_name.max' => 'Nama institusi maksimal 255 karakter.',
            'institution_address.string' => 'Alamat institusi harus berupa teks.',
            'institution_address.max' => 'Alamat institusi maksimal 255 karakter.',
            'institution_phone.string' => 'Telepon institusi harus berupa teks.',
            'institution_phone.max' => 'Telepon institusi maksimal 20 karakter.',
            'institution_email.email' => 'Email institusi harus berupa email yang valid.',
            'institution_email.max' => 'Email institusi maksimal 255 karakter.',
            'institution_website.url' => 'Website institusi harus berupa URL yang valid.',
            'institution_website.max' => 'Website institusi maksimal 255 karakter.',
            'institution_logo.string' => 'Logo institusi harus berupa teks.',
            'institution_logo.max' => 'Logo institusi maksimal 255 karakter.',
            'institution_banner.string' => 'Banner institusi harus berupa teks.',
            'institution_banner.max' => 'Banner institusi maksimal 255 karakter.',
            'institution_status.in' => 'Status institusi harus aktif atau tidak aktif.',
            'institution_description.required' => 'Deskripsi institusi wajib diisi.',
            'institution_description.string' => 'Deskripsi institusi harus berupa teks.',
            'headmaster_id.required' => 'ID kepala sekolah wajib diisi.',
            'headmaster_id.exists' => 'ID kepala sekolah tidak valid.',
        ];
    }
}
