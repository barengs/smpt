<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\TransactionType;
use Illuminate\Http\Request;

class TransactionTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $transactionTypes = TransactionType::paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Data jenis transaksi berhasil diambil',
                'data' => $transactionTypes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data jenis transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'code' => 'required|string|unique:transaction_types,code',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|in:transfer,payment,cash_operation,fee',
                'is_debit' => 'required|boolean',
                'is_credit' => 'required|boolean',
                'default_debit_coa' => 'required|string',
                'default_credit_coa' => 'required|string',
                'is_active' => 'boolean'
            ]);

            $transactionType = TransactionType::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Jenis transaksi berhasil dibuat',
                'data' => $transactionType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat jenis transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $transactionType = TransactionType::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Data jenis transaksi berhasil ditemukan',
                'data' => $transactionType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menemukan data jenis transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $transactionType = TransactionType::findOrFail($id);

            $validatedData = $request->validate([
                'code' => 'required|string|unique:transaction_types,code,' . $id,
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'required|in:transfer,payment,cash_operation,fee',
                'is_debit' => 'required|boolean',
                'is_credit' => 'required|boolean',
                'default_debit_coa' => 'required|string',
                'default_credit_coa' => 'required|string',
                'is_active' => 'boolean'
            ]);

            $transactionType->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Jenis transaksi berhasil diperbarui',
                'data' => $transactionType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui jenis transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $transactionType = TransactionType::findOrFail($id);

            // Check if transaction type has related transactions
            if ($transactionType->transactions()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat menghapus jenis transaksi karena masih memiliki transaksi terkait'
                ], 400);
            }

            $transactionType->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Jenis transaksi berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus jenis transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
