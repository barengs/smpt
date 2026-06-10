<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\Student;
use App\Models\Registration;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Aktivasi transaksi keuangan di Bank Santri dan pemicu side-effects di SMPT
     */
    public function activate(Request $request, string $id)
    {
        $bankUrl = config('services.bank_santri.url');
        $bankInternalKey = config('services.bank_santri.internal_key');

        try {
            // 1. Hubungi Bank Santri untuk aktivasi finansial
            $response = Http::withHeaders([
                'X-Internal-Key' => $bankInternalKey,
                'Accept'         => 'application/json',
            ])->put("{$bankUrl}/api/internal/transaction/{$id}/activate", $request->all());

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal aktivasi di Bank Santri: ' . ($response->json('message') ?? $response->body())
                ], $response->status());
            }

            $trxData = $response->json('data');
            $ref = $trxData['reference_number'] ?? null;

            Log::info('Activation successful in bank-santri', ['trx_id' => $id, 'ref_from_bank' => $ref]);

            if ($ref && str_starts_with($ref, 'REG')) {
                DB::beginTransaction();
                try {
                    $registration = Registration::where('registration_number', $ref)->first();
                    Log::info('Found registration record', ['ref' => $ref, 'found' => !!$registration]);
                    
                    if ($registration) {
                        $registration->update([
                            'payment_status' => 'completed',
                            'status'         => 'accepted'
                        ]);
                        
                        // Cek Santri terkait
                        $student = Student::where('nik', $registration->nik)->first();
                        
                        if (!$student) {
                            Log::info('Student record missing, creating now...', ['nik' => $registration->nik]);
                            
                            $activeYear = AcademicYear::where('active', true)->first();
                            $year = $activeYear ? $activeYear->year : date('Y');
                            
                            $student = Student::create([
                                'parent_id' => $registration->parent_id,
                                'nis' => $this->generateNis($year),
                                'period' => $year,
                                'first_name' => $registration->first_name,
                                'last_name' => $registration->last_name,
                                'gender' => $registration->gender,
                                'address' => $registration->address,
                                'nik' => $registration->nik,
                                'kk' => $registration->kk,
                                'born_in' => $registration->born_in,
                                'born_at' => $registration->born_at,
                                'village_id' => $registration->village_id,
                                'photo' => $registration->photo,
                                'program_id' => $registration->program_id,
                                'user_id' => Auth::id(),
                                'education_type_id' => $registration->education_level_id,
                                'status' => 'Aktif',
                            ]);
                        } else {
                            Log::info('Student record found, activating...', ['nik' => $registration->nik]);
                            $student->update(['status' => 'Aktif']);
                        }
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Gagal update status pendaftaran/santri: ' . $e->getMessage());
                    // Tidak returan error di sini karena uang SUDAH masuk di bank-santri.
                }
            } else {
                Log::warning('No REG reference found for activation side-effects', ['ref' => $ref]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Konfirmasi pembayaran berhasil. Status santri dan pendaftaran telah diperbarui.',
                'data'    => $trxData
            ]);

        } catch (\Exception $e) {
            Log::error('Activation Error in SMPT: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Callback dari Bank Santri setelah transaksi berhasil diaktivasi secara internal
     */
    public function activateCallback(Request $request)
    {
        $expectedKey = config('services.bank_santri.internal_key');
        $providedKey = $request->header('X-Internal-Key');

        if (empty($expectedKey) || $providedKey !== $expectedKey) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Invalid Internal API Key.',
            ], 401);
        }

        $request->validate([
            'reference_number' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        $ref = $request->reference_number;

        if ($ref && str_starts_with($ref, 'REG')) {
            DB::beginTransaction();
            try {
                $registration = Registration::where('registration_number', $ref)->first();
                Log::info('Found registration record in callback', ['ref' => $ref, 'found' => !!$registration]);
                
                if ($registration) {
                    $registration->update([
                        'payment_status' => 'completed',
                        'status'         => 'accepted'
                    ]);
                    
                    // Cek Santri terkait
                    $student = Student::where('nik', $registration->nik)->first();
                    
                    if (!$student) {
                        Log::info('Student record missing, creating now...', ['nik' => $registration->nik]);
                        
                        $activeYear = AcademicYear::where('active', true)->first();
                        $year = $activeYear ? $activeYear->year : date('Y');
                        
                        $student = Student::create([
                            'parent_id' => $registration->parent_id,
                            'nis' => $this->generateNis($year),
                            'period' => $year,
                            'first_name' => $registration->first_name,
                            'last_name' => $registration->last_name,
                            'gender' => $registration->gender,
                            'address' => $registration->address,
                            'nik' => $registration->nik,
                            'kk' => $registration->kk,
                            'born_in' => $registration->born_in,
                            'born_at' => $registration->born_at,
                            'village_id' => $registration->village_id,
                            'photo' => $registration->photo,
                            'program_id' => $registration->program_id,
                            'user_id' => null, // Backend service-to-service callback
                            'education_type_id' => $registration->education_level_id,
                            'status' => 'Aktif',
                        ]);
                    } else {
                        Log::info('Student record found, activating...', ['nik' => $registration->nik]);
                        $student->update(['status' => 'Aktif']);
                    }
                }
                DB::commit();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Status pendaftaran dan santri berhasil diperbarui.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Callback: Gagal update status pendaftaran/santri: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal update status pendaftaran/santri: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid reference number'
        ], 400);
    }

    /**
     * Copy of generateNis from RegistrationController to ensure consistency
     */
    private function generateNis($year)
    {
        $prefix = $year . '0197';
        $lastStudent = Student::where('nis', 'like', $prefix . '%')->orderBy('nis', 'desc')->first();

        if ($lastStudent) {
            $lastSequence = (int) substr($lastStudent->nis, -3);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
    }
}
