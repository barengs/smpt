<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\User;
use App\Models\Account;
use App\Models\Product;
use App\Models\Program;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\Registration;
use Illuminate\Http\Request;
use App\Models\ParentProfile;
use App\Models\TransactionType;
use App\Models\TransactionLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravolt\Indonesia\Models\City;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravolt\Indonesia\Models\Village;
use Illuminate\Support\Facades\Storage;
use App\Models\AcademicYear;
use App\Http\Resources\RegistrationResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RegistrationController extends Controller
{
    /**
     * Menampilkan daftar semua pendaftaran santri
     *
     * Method ini digunakan untuk mengambil semua data pendaftaran santri dari database
     * beserta relasi data orang tua. Data diurutkan berdasarkan tanggal terbaru
     * dan menggunakan pagination.
     *
     * @group Registration
     * @authenticated
     *
     * @queryParam page integer Halaman yang akan ditampilkan. Example: 1
     * @queryParam per_page integer Jumlah data per halaman. Example: 10
     *
     * @response 200 {
     *   "message": "Registrations fetched successfully",
     *   "status": 200,
     *   "data": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "registration_number": "REG2024001",
     *         "first_name": "Ahmad",
     *         "last_name": "Santri",
     *         "nis": "1234567890",
     *         "status": "pending",
     *         "parent": {
     *           "id": 1,
     *           "first_name": "Bapak",
     *           "last_name": "Ahmad",
     *           "nik": "1234567890123456"
     *         },
     *         "created_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ],
     *     "total": 50,
     *     "per_page": 10
     *   }
     * }
     */
    public function index()
    {
        try {
            $registrations = Registration::with(['parent', 'program'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return new RegistrationResource('Registrations fetched successfully', $registrations, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch registrations: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'wali_nama_depan' => 'required',
            'santri_nama_depan' => 'required',
            'santri_nisn' => 'required',
            'wali_nik' => 'required|min:16|max:16',
        ]);

        DB::beginTransaction();

        try {

            $checkParent = ParentProfile::where('nik', $request->wali_nik)->first();

            if (!$checkParent) {
                // Assuming you have a User model and it is set up correctly
                $user = User::create([
                    'name' => $request->wali_nama_depan,
                    'email' => $request->wali_email ?? $request->wali_nik,
                    'password' => bcrypt('password'),
                ]);

                $parent = $user->parent()->create([
                    'first_name' => $request->wali_nama_depan,
                    'last_name' => $request->wali_nama_belakang,
                    'nik' => $request->wali_nik,
                    'kk' => $request->wali_kk,
                    'phone' => $request->wali_telepon,
                    'email' => $request->wali_email,
                    'gender' => $request->wali_jenis_kelamin,
                    'parent_as' => $request->wali_sebagai,
                    'card_address' => $request->wali_alamamat_ktp,
                    'domicile_address' => $request->wali_alamat_domisili,
                    'occupation_id' => $request->wali_pekerjaan_id,
                    'education' => $request->wali_pendidikan_id,
                ]);

                if ($parent) {
                    $user->assignRole('orangtua');
                }
            }

            if ($request->hasFile('dokumen_foto_santri')) {
                $file = $request->file('dokumen_foto_santri');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads/registration_files', $fileName, 'public');
            }

            $registration = Registration::create([
                'registration_number' => $this->createRegistNumber(),
                'parent_id' => $checkParent ? $checkParent->nik : $request->wali_nik,
                'nis' => $request->santri_nisn,
                'first_name' => $request->santri_nama_depan,
                'last_name' => $request->santri_nama_belakang,
                'nik' => $request->santri_nik,
                'kk' => $request->wali_kk,
                'gender' => $request->santri_jenis_kelamin,
                'address' => $request->santri_alamat,
                'born_in' => $request->santri_tempat_lahir,
                'born_at' => $request->santri_tanggal_lahir,
                'village_id' => $request->desaId ?? null,
                'photo' => $filePath ?? null,
                'program_id' => $request->program_id ?? null,
                'previous_school' => $request->pendidikan_sekolah_asal,
                'previous_school_address' => $request->pendidikan_alamat_sekolah,
                'certificate_number' => $request->pendidikan_nomor_ijazah,
                'education_level_id' => $request->pendidikan_jenjang_sebelumnya,
                'previous_madrasah' => $request->madrasah_sekolah_asal,
                'previous_madrasah_address' => $request->madrasah_alamat_sekolah,
                'certificate_madrasah' => $request->madrasah_nomor_ijazah,
                'madrasah_level_id' => $request->madrasah_jenjang_sebelumnya,

            ]);

            if ($request->hasFile('dokumen_ijazah')) {
                $ijazahFile = $request->file('dokumen_ijazah');
                $ijazahFileName = time() . '_' . $ijazahFile->getClientOriginalName();
                $ijazahFilePath = $ijazahFile->storeAs('uploads/registration_files', $ijazahFileName, 'public');

                $registration->files()->create([
                    'file_name' => $ijazahFileName,
                    'file_path' => $ijazahFilePath,
                ]);
            }

            if ($request->hasFile('dokumen_opsional')) {
                foreach ($request->file('dokumen_opsional') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('uploads/registration_files', $fileName, 'public');

                    $registration->files()->create([
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                    ]);
                }
            }

            DB::commit();

            return new RegistrationResource('Registration successful', $registration->load('parent'), 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json('Data not found', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = Registration::with(['parent', 'files'])->findOrFail($id);
            $data->photo_url = Storage::url($data->photo);
            return new RegistrationResource('Data found', $data, 200);
        } catch (\Throwable $th) {
            return response()->json('Data not found: ' . $th->getMessage(), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $registration = Registration::findOrFail($id);
            $parent = ParentProfile::where('nik', $registration->parent_id)->firstOrFail();

            // Update Parent Profile
            $parent->update([
                'first_name' => $request->input('wali_nama_depan', $parent->first_name),
                'last_name' => $request->input('wali_nama_belakang', $parent->last_name),
                'kk' => $request->input('wali_kk', $parent->kk),
                'phone' => $request->input('wali_telepon', $parent->phone),
                'email' => $request->input('wali_email', $parent->email),
                'gender' => $request->input('wali_jenis_kelamin', $parent->gender),
                'parent_as' => $request->input('wali_sebagai', $parent->parent_as),
                'card_address' => $request->input('wali_alamamat_ktp', $parent->card_address),
                'domicile_address' => $request->input('wali_alamat_domisili', $parent->domicile_address),
                'occupation_id' => $request->input('wali_pekerjaan_id', $parent->occupation_id),
                'education' => $request->input('wali_pendidikan_id', $parent->education),
            ]);

            // Handle Santri Photo Upload
            if ($request->hasFile('dokumen_foto_santri')) {
                // Delete old photo if it exists
                if ($registration->photo && Storage::disk('public')->exists($registration->photo)) {
                    Storage::disk('public')->delete($registration->photo);
                }
                $file = $request->file('dokumen_foto_santri');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads/registration_files', $fileName, 'public');
                $registration->photo = $filePath;
            }

            // Update Registration
            $registration->update([
                'nis' => $request->input('santri_nisn', $registration->nis),
                'first_name' => $request->input('santri_nama_depan', $registration->first_name),
                'last_name' => $request->input('santri_nama_belakang', $registration->last_name),
                'nik' => $request->input('santri_nik', $registration->nik),
                'kk' => $request->input('wali_kk', $registration->kk),
                'gender' => $request->input('santri_jenis_kelamin', $registration->gender),
                'address' => $request->input('santri_alamat', $registration->address),
                'born_in' => $request->input('santri_tempat_lahir', $registration->born_in),
                'born_at' => $request->input('santri_tanggal_lahir', $registration->born_at),
                'village_id' => $request->input('desaId', $registration->village_id),
                'photo' => $request->hasFile('dokumen_foto_santri') ? $filePath : $registration->photo,
                'program_id' => $request->input('program_id', $registration->program_id),
                'previous_school' => $request->input('pendidikan_sekolah_asal', $registration->previous_school),
                'previous_school_address' => $request->input('pendidikan_alamat_sekolah', $registration->previous_school_address),
                'certificate_number' => $request->input('pendidikan_nomor_ijazah', $registration->certificate_number),
                'education_level_id' => $request->input('pendidikan_jenjang_sebelumnya', $registration->education_level_id),
                'previous_madrasah' => $request->input('madrasah_sekolah_asal', $registration->previous_madrasah),
                'previous_madrasah_address' => $request->input('madrasah_alamat_sekolah', $registration->previous_madrasah_address),
                'certificate_madrasah' => $request->input('madrasah_nomor_ijazah', $registration->certificate_madrasah),
                'madrasah_level_id' => $request->input('madrasah_jenjang_sebelumnya', $registration->madrasah_level_id),
            ]);
            $registration->save();


            // Handle Ijazah Upload
            if ($request->hasFile('dokumen_ijazah')) {
                // Optional: Delete old ijazah if needed
                $ijazahFile = $request->file('dokumen_ijazah');
                $ijazahFileName = time() . '_' . $ijazahFile->getClientOriginalName();
                $ijazahFilePath = $ijazahFile->storeAs('uploads/registration_files', $ijazahFileName, 'public');

                $registration->files()->updateOrCreate(
                    ['file_type' => 'ijazah'],
                    [
                        'file_name' => $ijazahFileName,
                        'file_path' => $ijazahFilePath,
                    ]
                );
            }

            // Handle Optional Documents
            if ($request->hasFile('dokumen_opsional')) {
                // Optional: Delete old optional files if needed
                foreach ($request->file('dokumen_opsional') as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('uploads/registration_files', $fileName, 'public');

                    $registration->files()->create([
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'file_type' => 'optional'
                    ]);
                }
            }

            DB::commit();

            return new RegistrationResource('Registration updated successfully', $registration->load('parent'), 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Registration not found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function createRegistNumber()
    {
        $lastRegistration = Registration::orderBy('created_at', 'desc')->first();
        if (!$lastRegistration) {
            $registrationNumber = 'REG' . date('Y') . '001';
        } else {
            $lastNumber = substr($lastRegistration->registration_number, -3);
            $nextNumber = str_pad((int) $lastNumber + 1, 3, '0', STR_PAD_LEFT);
            $registrationNumber = 'REG' . date('Y') . $nextNumber;
        }
        return $registrationNumber;
    }
    public function getByCurrentYear()
    {
        try {
            $registrations = Registration::with('parent')
                ->whereYear('created_at', date('Y'))
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return new RegistrationResource('Registrations for the current year fetched successfully', $registrations, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch registrations: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Membuat transaksi pembayaran registrasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRequestTransaction(Request $request)
    {
        $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'product_id' => 'required|exists:products,id',
            'hijri_year' => 'required|digits:4',
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'channel' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $registration = Registration::findOrFail($request->registration_id);

            // Get the active academic year
            $activeAcademicYear = AcademicYear::where('active', true)->first();
            $academicYear = $activeAcademicYear ? $activeAcademicYear->year : $request->hijri_year;

            // Create student
            $student = Student::create([
                'parent_id' => $registration->parent_id,
                'nis' => $this->generateNis($request->hijri_year),
                'period' => $academicYear, // Use active academic year instead of hijri year
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
                'status' => 'Tidak Aktif', // Default status
            ]);

            // Create account using direct model creation instead of calling controller method
            $account = Account::create([
                'account_number' => $student->nis,
                'customer_id' => $student->id,
                'product_id' => $request->product_id,
                'balance' => 0,
                'status' => 'TIDAK AKTIF',
                'open_date' => now(),
            ]);

            // get product | front-end harus mengirim id product
            $product = Product::findOrFail($request->product_id);
            // get program
            $program = Program::findOrFail($registration->program_id);
            // get transaction type | front-end harus mengirim id transaction type
            // di gunakan untuk membuat transaction ledger
            $transactionType = TransactionType::findOrFail($request->transaction_type_id);
            // Create transaction
            $transaction = Transaction::create([
                'id' => Str::uuid(),
                'transaction_type_id' => $request->transaction_type_id,
                'description' => 'Biaya Pendaftaran',
                'amount' => $product->opening_fee,
                'status' => 'PENDING',
                'reference_number' => $registration->registration_number,
                'channel' => $request->channel,
                'source_account' => $account->account_number,
                'destination_account' => null,
            ]);

            // create AccountMovement
            // AccountMovement::create([
            //     'account_number' => $account['account_number'],
            //     'transaction_id' => $transaction->id,
            //     'description' => 'Biaya Pendaftaran',
            //     'debit_amount' => $product->opening_fee,
            //     'credit_amount' => 0,
            //     'balance_after_movement' => $product->opening_fee
            // ]);

            // create TrasactionLeadger
            TransactionLedger::create([
                'transaction_id' => $transaction->id,
                'coa_code' => $transactionType->default_debit_coa,
                'amount' => $product->opening_fee,
                'type' => 'debit',
            ]);

            TransactionLedger::create([
                'transaction_id' => $transaction->id,
                'coa_code' => $transactionType->default_credit_coa,
                'amount' => $product->opening_fee,
                'type' => 'credit',
            ]);

            $registration->update([
                'payment_status' => 'pending',
                'payment_amount' => $product->opening_fee
            ]);

            DB::commit();

            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaksi Pendaftaran Gagal', [
                'error' => $e->getMessage(),
                'registration_id' => $request->registration_id,
            ]);
            return response()->json(['message' => 'Gagal membuat transaksi pendaftaran', 'error' => $e->getMessage()], 500);
        }
    }

    private function generateNis($hijriYear)
    {
        $prefix = $hijriYear . '0197';
        $lastStudent = Student::where('nis', 'like', $prefix . '%')->orderBy('nis', 'desc')->first();

        if ($lastStudent) {
            $lastSequence = (int) substr($lastStudent->nis, -3);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * ambil data tanggal lahir dan tempat lahir berdasarkan nik
     * @param mixed $nik
     * @return array
     */
    public function checkTtl($nik)
    {
        // Validasi NIK harus 16 digit
        if (strlen($nik) !== 16 || !is_numeric($nik)) {
            return [
                'success' => false,
                'message' => 'NIK harus terdiri dari 16 digit angka'
            ];
        }

        try {
            // Ekstrak komponen NIK
            $kodeKota = substr($nik, 0, 4); // 4 digit pertama: kode kota/kabupaten
            $districtCode = substr($nik, 0, 6); // 6 digit pertama: kode kecamatan
            $tanggalLahir = substr($nik, 6, 6); // digit 7-12: tanggal lahir (ddmmyy)
            $digitTanggal = substr($nik, 6, 2); // digit 7-8: tanggal lahir

            // Tentukan jenis kelamin berdasarkan digit tanggal
            $jenisKelamin = 'Laki-laki'; // default
            if ((int) $digitTanggal > 40) {
                $jenisKelamin = 'Perempuan';
            }

            // Ekstrak tanggal lahir
            $tanggal = substr($tanggalLahir, 0, 2); // dd
            $bulan = substr($tanggalLahir, 2, 2); // mm
            $tahun = substr($tanggalLahir, 4, 2); // yy

            // Konversi tahun ke format lengkap (asumsi tahun 2000-an jika yy < 30, 1900-an jika lebih besar)
            $tahunLengkap = (int) $tahun < 30 ? '20' . $tahun : '19' . $tahun;

            // Format tanggal lahir lengkap
            $tanggalLahirFormatted = $tahunLengkap . '-' . $bulan . '-' . $tanggal;

            // Validasi tanggal lahir yang valid
            $tanggalCheck = \DateTime::createFromFormat('Y-m-d', $tanggalLahirFormatted);
            if (!$tanggalCheck || $tanggalCheck->format('Y-m-d') !== $tanggalLahirFormatted) {
                return [
                    'success' => false,
                    'message' => 'Format tanggal lahir tidak valid dari NIK'
                ];
            }

            // Cari data kota berdasarkan kode kota menggunakan model City dari Laravolt
            $city = City::where('code', $kodeKota)->first();
            $tempatLahir = $city ? $city->name : 'Kota tidak ditemukan (kode: ' . $kodeKota . ')';

            // Cari data desa berdasarkan district_code menggunakan model Village dari Laravolt
            $villages = Village::where('district_code', $districtCode)->get();
            $desaData = [];
            if ($villages->isNotEmpty()) {
                foreach ($villages as $village) {
                    $desaData[] = [
                        'id' => $village->id,
                        'code' => $village->code,
                        'name' => $village->name,
                        'district_name' => $village->district ? $village->district->name : '',
                        'city_name' => $village->district && $village->district->city ? $village->district->city->name : '',
                        'province_name' => $village->district && $village->district->city && $village->district->city->province ? $village->district->city->province->name : ''
                    ];
                }
            }

            return [
                'success' => true,
                'jenis_kelamin' => $jenisKelamin,
                'tanggal_lahir' => $tanggalLahirFormatted,
                'kode_kota' => $kodeKota,
                'tempat_lahir' => $tempatLahir,
                'district_code' => $districtCode,
                'desa' => $desaData,
                'nik' => $nik
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error memproses NIK: ' . $e->getMessage()
            ];
        }
    }
}
