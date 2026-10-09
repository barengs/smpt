<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exam Clearance Policy (Kebijakan Ujian Triwulan)
    |--------------------------------------------------------------------------
    |
    | Kebijakan ini bersifat DINAMIS dan berada di SMPT (domain pendidikan).
    | Bank-santri hanya menyediakan fakta tunggakan; SMPT yang menilai
    | apakah santri layak mengikuti ujian berdasarkan kebijakan di bawah.
    |
    */

    // Jumlah bulan menunggak maksimal yang masih ditoleransi.
    // 0 = tidak boleh menunggak sama sekali untuk ujian triwulan.
    'max_overdue_months' => (int) env('EXAM_MAX_OVERDUE_MONTHS', 1),

    // Toleransi nominal tunggakan (Rp). 0 = tanpa toleransi nominal.
    'max_arrears_amount' => (float) env('EXAM_MAX_ARREARS_AMOUNT', 0),

    // Izinkan panitia/kepala sekolah memberi dispensasi (komitmen bayar).
    'allow_dispensation' => (bool) env('EXAM_ALLOW_DISPENSATION', true),

    // Cek keuangan gagal (bank-santri down) → tetap izinkan ujian.
    // Set false jika ingin fail-closed (blokir sampai data tersedia).
    'fail_open' => (bool) env('EXAM_FAIL_OPEN', true),

];
