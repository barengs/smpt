<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_date' => 'required|date',
            'report_time' => 'nullable|date_format:H:i',
            'report_notes' => 'nullable|string',
            'condition' => 'required|in:sehat,sakit,lainnya',
            'reported_to' => 'nullable|exists:staff,id',
        ];
    }

    public function messages(): array
    {
        return [
            'report_date.required' => 'Tanggal laporan harus diisi',
            'condition.required' => 'Kondisi saat kembali harus diisi',
            'condition.in' => 'Kondisi harus salah satu dari: sehat, sakit, lainnya',
        ];
    }
}
