<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegistrationTemplateExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Return sample data
        return [
            [
                '1234567890123456', // wali_nik
                'Ahmad',            // wali_nama_depan
                'Fauzi',            // wali_nama_belakang
                '1234567890123456', // wali_kk
                '081234567890',      // wali_telepon
                'wali@example.com',  // wali_email
                'L',                // wali_jenis_kelamin
                'ayah',             // wali_sebagai (ayah/ibu/wali)
                'Jl. Contoh KTP',   // wali_alamat_ktp
                'Jl. Contoh Domisili', // wali_alamat_domisili
                '1',                // wali_pekerjaan_id
                '1',                // wali_pendidikan_id
                '2026001',          // santri_nisn
                'Muhammad',         // santri_nama_depan
                'Ali',              // santri_nama_belakang
                '3201010101010101', // santri_nik
                'L',                // santri_jenis_kelamin (L/P)
                'Jl. Contoh Domisili', // santri_alamat
                'Jakarta',          // santri_tempat_lahir
                '2015-05-20',       // santri_tanggal_lahir (YYYY-MM-DD)
                '3201010001',       // santri_desa_code
                '081234567890',     // santri_telepon
                '60111',            // santri_kode_pos
                '1',                // program_id
                '2026',             // period
                'SD Negeri 1',      // pendidikan_sekolah_asal
                'Jl. Sekolah 1',    // pendidikan_alamat_sekolah
                'DN-01/12345',      // pendidikan_nomor_ijazah
                '1',                // pendidikan_jenjang_sebelumnya
                '',                 // madrasah_sekolah_asal
                '',                 // madrasah_alamat_sekolah
                '',                 // madrasah_nomor_ijazah
                '',                 // madrasah_jenjang_sebelumnya
                'pending'           // status
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'wali_nik',
            'wali_nama_depan',
            'wali_nama_belakang',
            'wali_kk',
            'wali_telepon',
            'wali_email',
            'wali_jenis_kelamin',
            'wali_sebagai',
            'wali_alamat_ktp',
            'wali_alamat_domisili',
            'wali_pekerjaan_id',
            'wali_pendidikan_id',
            'santri_nisn',
            'santri_nama_depan',
            'santri_nama_belakang',
            'santri_nik',
            'santri_jenis_kelamin',
            'santri_alamat',
            'santri_tempat_lahir',
            'santri_tanggal_lahir',
            'santri_desa_code',
            'santri_telepon',
            'santri_kode_pos',
            'program_id',
            'period',
            'pendidikan_sekolah_asal',
            'pendidikan_alamat_sekolah',
            'pendidikan_nomor_ijazah',
            'pendidikan_jenjang_sebelumnya',
            'madrasah_sekolah_asal',
            'madrasah_alamat_sekolah',
            'madrasah_nomor_ijazah',
            'madrasah_jenjang_sebelumnya',
            'status'
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // wali_nik
            'B' => 20,  // wali_nama_depan
            'C' => 20,  // wali_nama_belakang
            'D' => 20,  // wali_kk
            'E' => 15,  // wali_telepon
            'F' => 25,  // wali_email
            'G' => 18,  // wali_jenis_kelamin
            'H' => 15,  // wali_sebagai
            'I' => 30,  // wali_alamat_ktp
            'J' => 30,  // wali_alamat_domisili
            'K' => 18,  // wali_pekerjaan_id
            'L' => 18,  // wali_pendidikan_id
            'M' => 15,  // santri_nisn
            'N' => 20,  // santri_nama_depan
            'O' => 20,  // santri_nama_belakang
            'P' => 20,  // santri_nik
            'Q' => 20,  // santri_jenis_kelamin
            'R' => 30,  // santri_alamat
            'S' => 20,  // santri_tempat_lahir
            'T' => 20,  // santri_tanggal_lahir
            'U' => 18,  // santri_desa_code
            'V' => 18,  // santri_telepon
            'W' => 15,  // santri_kode_pos
            'X' => 12,  // program_id
            'Y' => 10,  // period
            'Z' => 25,  // pendidikan_sekolah_asal
            'AA' => 30, // pendidikan_alamat_sekolah
            'AB' => 25, // pendidikan_nomor_ijazah
            'AC' => 30, // pendidikan_jenjang_sebelumnya
            'AD' => 25, // madrasah_sekolah_asal
            'AE' => 30, // madrasah_alamat_sekolah
            'AF' => 25, // madrasah_nomor_ijazah
            'AG' => 30, // madrasah_jenjang_sebelumnya
            'AH' => 12, // status
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
