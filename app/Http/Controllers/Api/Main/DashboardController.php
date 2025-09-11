<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\Staff;
use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $santri = Student::where("status", 'Aktif')->count();
            $asatidz = Staff::count();
            $tugasan = Student::where("status", 'Tugas')->count();
            $alumni = Student::where("status", 'Alumni')->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    // Existing data for pesantren system
                    'santri' => $santri,
                    'asatidz' => $asatidz,
                    'tugasan' => $tugasan,
                    'alumni' => $alumni,
                ]
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
        // hitung data santri (student) baru

        // hitung data santri (student) aktif

        // hitung jumlah staff

    }

    public function transactionStatistics(Request $request)
    {
        try {
            $request->validate([
                'period' => 'required|in:daily,weekly,monthly,yearly',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $period = $request->period;
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // Build query based on period
            $query = Transaction::query();

            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            } else {
                switch ($period) {
                    case 'daily':
                        $query->whereDate('created_at', today());
                        break;
                    case 'weekly':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'monthly':
                        $query->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year);
                        break;
                    case 'yearly':
                        $query->whereYear('created_at', now()->year);
                        break;
                }
            }

            $totalTransactions = $query->count();
            $totalAmount = $query->sum('amount');
            $averageAmount = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

            // Transaction types breakdown
            $transactionTypes = $query->with('transactionType')->selectRaw('transaction_type_id, COUNT(*) as count')
                ->groupBy('transaction_type_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->transactionType->name => $item->count];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction statistics retrieved successfully',
                'data' => [
                    'period' => $period,
                    'total_transactions' => $totalTransactions,
                    'total_amount' => $totalAmount,
                    'average_amount' => round($averageAmount, 2),
                    'transaction_types' => $transactionTypes,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve transaction statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Menampilkan statistik santri berdasarkan periode (angkatan).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function studentStatisticsByPeriod(Request $request)
    {
        try {
            $statistics = Student::select('period', DB::raw('count(*) as total'))
                ->groupBy('period')
                ->orderBy('period', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Student statistics by period retrieved successfully',
                'data' => $statistics,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve student statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
}
