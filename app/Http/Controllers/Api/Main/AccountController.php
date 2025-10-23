<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\Account;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\AccountResource;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = Account::with(['customer', 'product'])->get();
        return new AccountResource('Data akun berhasil diambil', $accounts, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $student = Student::findOrFail($request->student_id);
            // Check if the student already has an account
            if (Account::where('customer_id', $student->id)->exists()) {
                return new AccountResource('Siswa sudah memiliki akun', null, 409);
            }

            // Create a new account for the student
            $account = Account::create([
                'account_number' => $student->nis,
                'customer_id' => $student->id,
                'product_id' => $request->product_id,
                'balance' => 0,
                'status' => 'TIDAK AKTIF', // Default status
                'open_date' => now(),
            ]);

            return new AccountResource('Akun berhasil dibuat', $account, 201);
        } catch (\Exception $e) {
            return new AccountResource('Gagal membuat akun: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $account = Account::with(['customer', 'product', 'movements'])->where('account_number', $id)->firstOrFail();
            return new AccountResource('Data akun berhasil diambil', $account, 200);
        } catch (ModelNotFoundException $e) {
            return new AccountResource('Akun tidak ditemukan', null, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'status' => 'required|in:AKTIF,TIDAK AKTIF,TUTUP,TERBLOKIR,DIBEKUKAN',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $account = Account::where('account_number', $id)->firstOrFail();
            $account->update($request->only(['product_id', 'status']));
            return new AccountResource('Akun berhasil diperbarui', $account, 200);
        } catch (ModelNotFoundException $e) {
            return new AccountResource('Akun tidak ditemukan', null, 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $account = Account::findOrFail($id);

            // Check if account can be deleted
            if ($account->balance > 0) {
                return new AccountResource('Tidak dapat menghapus akun dengan saldo aktif', null, 409);
            }

            if ($account->movements()->exists()) {
                return new AccountResource('Tidak dapat menghapus akun dengan riwayat transaksi', null, 409);
            }

            $account->delete();
            return new AccountResource('Akun berhasil dihapus', null, 204);
        } catch (ModelNotFoundException $e) {
            return new AccountResource('Akun tidak ditemukan', null, 404);
        }
    }

    public function updateStatus(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:AKTIF,TIDAK AKTIF,TUTUP,TERBLOKIR,DIBEKUKAN',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $account = Account::findOrFail($id);

            // Additional validation for status changes
            if ($request->status === 'TUTUP' && $account->balance > 0) {
                return new AccountResource('Tidak dapat mengubah status menjadi TUTUP dengan saldo aktif', null, 409);
            }

            $account->status = $request->status;

            // Set close_date if status is CLOSED
            if ($request->status === 'TUTUP') {
                $account->close_date = now();
            } else {
                $account->close_date = null;
            }

            $account->save();
            return new AccountResource('Status akun berhasil diperbarui', $account, 200);
        } catch (ModelNotFoundException $e) {
            return new AccountResource('Akun tidak ditemukan', null, 404);
        }
    }
}
