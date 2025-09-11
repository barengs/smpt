<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\Account;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TransactionResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionController extends Controller
{
    /**
     * Menampilkan daftar semua transaksi keuangan pesantren
     *
     * Method ini digunakan untuk mengambil semua data transaksi keuangan pesantren dari database
     * beserta relasi account sumber, account tujuan, dan entri ledger.
     * Transaksi ini mencakup semua operasi keuangan santri seperti setoran, penarikan, dan transfer.
     *
     * @group Bank Santri
     * @authenticated
     *
     * @response 200 {
     *   "message": "data ditemukan",
     *   "status": 200,
     *   "data": [
     *     {
     *       "id": "550e8400-e29b-41d4-a716-446655440000",
     *       "transaction_type": "CASH_DEPOSIT",
     *       "description": "Setoran tunai santri",
     *       "amount": "1000000.00",
     *       "status": "SUCCESS",
     *       "reference_number": "DEP202412011234567890",
     *       "channel": "TELLER",
     *       "source_account": null,
     *       "destination_account": "1234567890",
     *       "created_at": "2024-12-01T12:34:56.000000Z",
     *       "updated_at": "2024-12-01T12:34:56.000000Z",
     *       "source_account": null,
     *       "destination_account": {
     *         "account_number": "1234567890",
     *         "customer_id": 1,
     *         "product_id": 1,
     *         "balance": "5000000.00",
     *         "status": "ACTIVE"
     *       }
     *     }
     *   ]
     * }
     *
     * @response 404 {
     *   "status": "error",
     *   "message": "No transactions found"
     * }
     */
    public function index()
    {
        try {
            // Fetch all transactions with related data
            $transactions = Transaction::with(['sourceAccount', 'destinationAccount', 'ledgerEntries'])->get();

            return new TransactionResource('data ditemukan', $transactions, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transactions: ' . $e->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No transactions found',
            ], 404);
        }
    }

    /**
     * Menyimpan transaksi baru ke database
     *
     * Method ini digunakan untuk membuat transaksi baru dengan validasi input
     * yang ketat. Transaksi akan dibuat dengan UUID unik dan referensi number
     * yang harus unik.
     *
     * @param \Illuminate\Http\Request $request Request yang berisi data transaksi
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat menyimpan data
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'transaction_type' => 'required|string|max:50',
                'description' => 'nullable|string',
                'amount' => 'required|numeric|min:0',
                'status' => 'required|in:SUCCESS,PENDING,FAILED,REVERSED',
                'reference_number' => 'required|string|max:50|unique:transactions,reference_number',
                'channel' => 'required|string|max:20',
                'source_account' => 'nullable|string|max:20|exists:accounts,account_number',
                'destination_account' => 'nullable|string|max:20|exists:accounts,account_number',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $transaction = Transaction::create([
                'id' => Str::uuid(),
                'transaction_type' => $request->transaction_type,
                'description' => $request->description,
                'amount' => $request->amount,
                'status' => $request->status,
                'reference_number' => $request->reference_number,
                'channel' => $request->channel,
                'source_account' => $request->source_account,
                'destination_account' => $request->destination_account,
            ]);

            return new TransactionResource('Transaction created successfully', $transaction, 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create transaction: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail transaksi berdasarkan ID
     *
     * Method ini digunakan untuk mengambil detail transaksi spesifik
     * beserta relasi account sumber, account tujuan, dan entri ledger.
     *
     * @param string $id ID transaksi yang akan ditampilkan
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat mengambil data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika transaksi tidak ditemukan
     */
    public function show(string $id)
    {
        try {
            $transaction = Transaction::with(['sourceAccount', 'destinationAccount', 'ledgerEntries'])->findOrFail($id);
            return new TransactionResource('data ditemukan', $transaction, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transaction: ' . $th->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
            ], 404);
        }
    }

    /**
     * Mengupdate data transaksi yang ada
     *
     * Method ini digunakan untuk mengubah data transaksi yang sudah ada
     * dengan validasi input yang ketat. Hanya field yang dikirim yang akan diupdate.
     *
     * @param \Illuminate\Http\Request $request Request yang berisi data yang akan diupdate
     * @param string $id ID transaksi yang akan diupdate
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat mengupdate data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika transaksi tidak ditemukan
     */
    public function update(Request $request, string $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'transaction_type' => 'sometimes|required|string|max:50',
                'description' => 'nullable|string',
                'amount' => 'sometimes|required|numeric|min:0',
                'status' => 'sometimes|required|in:SUCCESS,PENDING,FAILED,REVERSED',
                'reference_number' => 'sometimes|required|string|max:50|unique:transactions,reference_number,' . $id,
                'channel' => 'sometimes|required|string|max:20',
                'source_account' => 'nullable|string|max:20|exists:accounts,account_number',
                'destination_account' => 'nullable|string|max:20|exists:accounts,account_number',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $transaction->update($request->all());

            return new TransactionResource('Transaction updated successfully', $transaction, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update transaction: ' . $th->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
            ], 404);
        }
    }

    /**
     * Menghapus transaksi dari database
     *
     * Method ini digunakan untuk menghapus transaksi berdasarkan ID.
     * Perlu diperhatikan bahwa penghapusan transaksi harus dilakukan dengan hati-hati
     * karena dapat mempengaruhi audit trail.
     *
     * @param string $id ID transaksi yang akan dihapus
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat menghapus data
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika transaksi tidak ditemukan
     */
    public function destroy(string $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete transaction: ' . $th->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
            ], 404);
        }
    }

    /**
     * Memproses transaksi setoran tunai santri
     *
     * Method ini digunakan oleh teller untuk memproses setoran tunai ke rekening santri.
     * Method ini akan:
     * - Memvalidasi input santri
     * - Membuat record transaksi
     * - Mengupdate saldo rekening santri
     * - Menggunakan database transaction untuk menjaga konsistensi data
     *
     * @group Bank Santri
     * @authenticated
     *
     * @bodyParam account_number string required Nomor rekening santri tujuan. Example: 1234567890
     * @bodyParam amount numeric required Jumlah setoran (minimal 0). Example: 1000000
     * @bodyParam description string Deskripsi setoran. Example: Setoran tunai santri
     * @bodyParam teller_id string required ID teller yang memproses. Example: TEL001
     *
     * @response 201 {
     *   "message": "Cash deposit processed successfully",
     *   "status": 201,
     *   "data": {
     *     "id": "550e8400-e29b-41d4-a716-446655440000",
     *     "transaction_type": "CASH_DEPOSIT",
     *     "description": "Setoran tunai santri",
     *     "amount": "1000000.00",
     *     "status": "SUCCESS",
     *     "reference_number": "DEP202412011234567890",
     *     "channel": "TELLER",
     *     "destination_account": "1234567890",
     *     "created_at": "2024-12-01T12:34:56.000000Z",
     *     "updated_at": "2024-12-01T12:34:56.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "status": "error",
     *   "message": "Validation failed",
     *   "errors": {
     *     "account_number": ["The account number field is required."],
     *     "amount": ["The amount field is required."]
     *   }
     * }
     *
     * @response 404 {
     *   "status": "error",
     *   "message": "Account not found"
     * }
     */
    public function cashDeposit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'account_number' => 'required|string|exists:accounts,account_number',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'teller_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Create transaction record
                $transaction = Transaction::create([
                    'id' => Str::uuid(),
                    'transaction_type' => 'CASH_DEPOSIT',
                    'description' => $request->description ?? 'Cash deposit',
                    'amount' => $request->amount,
                    'status' => 'SUCCESS',
                    'reference_number' => 'DEP' . date('YmdHis') . rand(1000, 9999),
                    'channel' => 'TELLER',
                    'destination_account' => $request->account_number,
                ]);

                // Update account balance
                $account = Account::where('account_number', $request->account_number)->lockForUpdate()->first();
                $account->balance += $request->amount;
                $account->save();

                DB::commit();

                return new TransactionResource('Cash deposit processed successfully', $transaction, 201);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process cash deposit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memproses transaksi penarikan tunai
     *
     * Method ini digunakan oleh teller untuk memproses penarikan tunai dari rekening nasabah.
     * Method ini akan:
     * - Memvalidasi input nasabah
     * - Memeriksa saldo rekening (harus cukup)
     * - Membuat record transaksi
     * - Mengupdate saldo rekening nasabah
     * - Menggunakan database transaction untuk menjaga konsistensi data
     *
     * @param \Illuminate\Http\Request $request Request yang berisi data penarikan
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat memproses penarikan
     */
    public function cashWithdrawal(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'account_number' => 'required|string|exists:accounts,account_number',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'teller_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Check account balance
                $account = Account::where('account_number', $request->account_number)->lockForUpdate()->first();

                if ($account->balance < $request->amount) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Insufficient balance',
                    ], 400);
                }

                // Create transaction record
                $transaction = Transaction::create([
                    'id' => Str::uuid(),
                    'transaction_type' => 'CASH_WITHDRAWAL',
                    'description' => $request->description ?? 'Cash withdrawal',
                    'amount' => $request->amount,
                    'status' => 'SUCCESS',
                    'reference_number' => 'WTH' . date('YmdHis') . rand(1000, 9999),
                    'channel' => 'TELLER',
                    'source_account' => $request->account_number,
                ]);

                // Update account balance
                $account->balance -= $request->amount;
                $account->save();

                DB::commit();

                return new TransactionResource('Cash withdrawal processed successfully', $transaction, 201);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process cash withdrawal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memproses transaksi transfer dana
     *
     * Method ini digunakan oleh teller untuk memproses transfer dana antar rekening.
     * Method ini akan:
     * - Memvalidasi input nasabah
     * - Memeriksa saldo rekening sumber (harus cukup)
     * - Membuat record transaksi
     * - Mengupdate saldo kedua rekening (sumber dan tujuan)
     * - Menggunakan database transaction untuk menjaga konsistensi data
     *
     * @param \Illuminate\Http\Request $request Request yang berisi data transfer
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat memproses transfer
     */
    public function fundTransfer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'source_account' => 'required|string|exists:accounts,account_number',
                'destination_account' => 'required|string|exists:accounts,account_number',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'teller_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if ($request->source_account === $request->destination_account) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Source and destination accounts cannot be the same',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Check source account balance
                $sourceAccount = Account::where('account_number', $request->source_account)->lockForUpdate()->first();

                if ($sourceAccount->balance < $request->amount) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Insufficient balance in source account',
                    ], 400);
                }

                // Create transaction record
                $transaction = Transaction::create([
                    'id' => Str::uuid(),
                    'transaction_type' => 'FUND_TRANSFER',
                    'description' => $request->description ?? 'Fund transfer',
                    'amount' => $request->amount,
                    'status' => 'SUCCESS',
                    'reference_number' => 'TRF' . date('YmdHis') . rand(1000, 9999),
                    'channel' => 'TELLER',
                    'source_account' => $request->source_account,
                    'destination_account' => $request->destination_account,
                ]);

                // Update account balances
                $sourceAccount->balance -= $request->amount;
                $sourceAccount->save();

                $destinationAccount = Account::where('account_number', $request->destination_account)->lockForUpdate()->first();
                $destinationAccount->balance += $request->amount;
                $destinationAccount->save();

                DB::commit();

                return new TransactionResource('Fund transfer processed successfully', $transaction, 201);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process fund transfer: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil transaksi berdasarkan nomor rekening
     *
     * Method ini digunakan untuk melihat riwayat transaksi dari suatu rekening.
     * Method ini akan menampilkan semua transaksi yang melibatkan rekening tersebut,
     * baik sebagai rekening sumber maupun rekening tujuan.
     *
     * @param string $accountNumber Nomor rekening yang akan dicari transaksinya
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat mengambil data
     */
    public function getByAccount(string $accountNumber)
    {
        try {
            $transactions = Transaction::where('source_account', $accountNumber)
                ->orWhere('destination_account', $accountNumber)
                ->with(['sourceAccount', 'destinationAccount'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No transactions found for this account',
                ], 404);
            }

            return new TransactionResource('data ditemukan', $transactions, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transactions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil transaksi berdasarkan status
     *
     * Method ini digunakan untuk memfilter transaksi berdasarkan statusnya.
     * Status yang valid: SUCCESS, PENDING, FAILED, REVERSED
     * Method ini berguna untuk monitoring dan reporting transaksi.
     *
     * @param string $status Status transaksi yang akan difilter
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat mengambil data
     */
    public function getByStatus(string $status)
    {
        try {
            $validator = Validator::make(['status' => $status], [
                'status' => 'required|in:SUCCESS,PENDING,FAILED,REVERSED',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid status',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $transactions = Transaction::where('status', $status)
                ->with(['sourceAccount', 'destinationAccount'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No transactions found with this status',
                ], 404);
            }

            return new TransactionResource('data ditemukan', $transactions, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transactions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mengambil transaksi berdasarkan rentang tanggal
     *
     * Method ini digunakan untuk melihat transaksi dalam periode waktu tertentu.
     * Method ini berguna untuk:
     * - Laporan harian/bulanan/tahunan
     * - Audit transaksi
     * - Analisis pola transaksi
     *
     * @param \Illuminate\Http\Request $request Request yang berisi start_date dan end_date
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat mengambil data
     */
    public function getByDateRange(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $transactions = Transaction::whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ])
                ->with(['sourceAccount', 'destinationAccount'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No transactions found in this date range',
                ], 404);
            }

            return new TransactionResource('data ditemukan', $transactions, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transactions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Membalikkan/membatalkan transaksi
     *
     * Method ini digunakan untuk membalikkan transaksi yang sudah diproses.
     * Method ini akan:
     * - Membuat transaksi pembalikan (reversal)
     * - Mengembalikan saldo rekening ke kondisi sebelum transaksi
     * - Menandai transaksi asli sebagai REVERSED
     * - Menggunakan database transaction untuk menjaga konsistensi data
     *
     * @param string $id ID transaksi yang akan dibalikkan
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat membalikkan transaksi
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika transaksi tidak ditemukan
     */
    public function reverseTransaction(string $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            if ($transaction->status === 'REVERSED') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Transaction is already reversed',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Create reversal transaction
                $reversalTransaction = Transaction::create([
                    'id' => Str::uuid(),
                    'transaction_type' => $transaction->transaction_type . '_REVERSAL',
                    'description' => 'Reversal of transaction ' . $transaction->reference_number,
                    'amount' => $transaction->amount,
                    'status' => 'SUCCESS',
                    'reference_number' => 'REV' . date('YmdHis') . rand(1000, 9999),
                    'channel' => $transaction->channel,
                    'source_account' => $transaction->destination_account,
                    'destination_account' => $transaction->source_account,
                ]);

                // Update account balances based on transaction type
                if ($transaction->source_account) {
                    $sourceAccount = Account::where('account_number', $transaction->source_account)->lockForUpdate()->first();
                    $sourceAccount->balance += $transaction->amount;
                    $sourceAccount->save();
                }

                if ($transaction->destination_account) {
                    $destinationAccount = Account::where('account_number', $transaction->destination_account)->lockForUpdate()->first();
                    $destinationAccount->balance -= $transaction->amount;
                    $destinationAccount->save();
                }

                // Mark original transaction as reversed
                $transaction->status = 'REVERSED';
                $transaction->save();

                DB::commit();

                return new TransactionResource('Transaction reversed successfully', $reversalTransaction, 200);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reverse transaction: ' . $e->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
            ], 404);
        }
    }

    /**
     * Mengambil ringkasan transaksi untuk laporan
     *
     * Method ini digunakan untuk menghasilkan laporan ringkasan transaksi
     * dalam periode waktu tertentu. Method ini berguna untuk:
     * - Laporan manajemen
     * - Analisis kinerja
     * - Monitoring transaksi per jenis dan status
     * - Data untuk dashboard
     *
     * @param \Illuminate\Http\Request $request Request yang berisi start_date dan end_date
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception Jika terjadi kesalahan saat mengambil data
     */
    public function getTransactionSummary(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $summary = Transaction::whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ])
                ->selectRaw('
                transaction_type,
                status,
                channel,
                COUNT(*) as total_transactions,
                SUM(amount) as total_amount
            ')
                ->groupBy('transaction_type', 'status', 'channel')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction summary retrieved successfully',
                'data' => $summary,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get transaction summary: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Membuat transaksi pembayaran registrasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRegistrationPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'product_id' => 'required|exists:products,id',
            'hijri_year' => 'required|digits:4',
            'amount' => 'required|numeric|min:0',
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'registration_number' => 'required|unique:registrations,registration_number'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            // Create account
            $accountController = new AccountController();
            $accountRequest = new Request([
                'student_id' => $request->student_id,
                'product_id' => $request->product_id,
                'hijri_year' => $request->hijri_year,
            ]);
            $accountResponse = $accountController->store($accountRequest);

            if ($accountResponse->getStatusCode() != 201) {
                DB::rollBack();
                return $accountResponse;
            }

            $account = json_decode($accountResponse->getContent());

            // Create transaction
            $transaction = Transaction::create([
                'id' => Str::uuid(),
                'transaction_type_id' => $request->transaction_type_id,
                'description' => 'biaya pendaftaran',
                'amount' => $request->amount,
                'status' => 'PENDING',
                'reference_number' => $request->registration_number,
                'channel' => 'SYSTEM',
                'source_account' => $account->account_number,
                'destination_account' => null, // Or a specific account for registration fees
            ]);

            DB::commit();

            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create registration payment', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json(['message' => 'Failed to create registration payment', 'error' => $e->getMessage()], 500);
        }
    }

    public function activateTransaction(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            $transaction->status = 'SUCCESS';
            $transaction->save();

            $account = Account::where('account_number', $transaction->source_account)->first();

            if (!$account) {
                return response()->json(['message' => 'Account not found'], 404);
            }

            $account->status = 'AKTIF';
            $account->save();
            // customer_id merupakan id siswa
            $student = Student::where('id', $account->customer_id)->first();

            if (!$student) {
                return response()->json(['message' => 'Student not found'], 404);
            }

            $student->status = 'AKTIF';
            $student->save();

            $registration = Registration::where('registration_number', $transaction->reference_number)->first();

            if (!$registration) {
                return response()->json(['message' => 'Registration not found'], 404);
            }

            $registration->status = 'accepted';
            $registration->payment_status = 'completed';
            $registration->save();

            DB::commit();

            return response()->json(['message' => 'Transaction activated successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to activate transaction', [
                'error' => $th->getMessage(),
                'request' => $request->all(),
            ]);
            return response()->json(['message' => 'Failed to activate transaction', 'error' => $th->getMessage()], 500);
        }
    }

    public function deactivateTransaction(Request $request, $id)
    {
        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            $transaction->status = 'INACTIVE';
            $transaction->save();

            return response()->json(['message' => 'Transaction deactivated successfully'], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
