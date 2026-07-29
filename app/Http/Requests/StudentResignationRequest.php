<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentResignationRequest extends FormRequest
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
        $isPost = $this->isMethod('post');

        return [
            'student_id' => 'required|exists:students,id',
            'submission_type' => 'sometimes|in:biasa,pasca_tugas',
            'note' => 'nullable|string',
            'attachment' => ($isPost ? 'required' : 'nullable') . '|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'status' => 'sometimes|in:pending,proses,disetujui,ditolak',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Santri wajib dipilih',
            'student_id.exists' => 'Santri tidak ditemukan',
            'submission_type.in' => 'Tipe pengajuan tidak valid',
            'attachment.required' => 'Dokumen lampiran wajib diunggah',
            'attachment.file' => 'Lampiran harus berupa file',
            'attachment.mimes' => 'Format file harus berupa pdf, jpeg, png, atau jpg',
            'attachment.max' => 'Ukuran file maksimal 2MB',
            'status.in' => 'Status tidak valid',
        ];
    }
}
