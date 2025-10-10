<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\Account;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = Account::with(['customer', 'product'])->get();
        return response()->json($accounts);
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
                return response()->json(['message' => 'Student already has an account'], 409);
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

            return response()->json($account, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create account', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $account = Account::with(['customer', 'product', 'movements'])->where('account_number', $id)->firstOrFail();
            return response()->json($account);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Account not found'], 404);
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
            return response()->json($account);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Account not found'], 404);
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
                return response()->json(['message' => 'Cannot delete account with active balance'], 409);
            }

            if ($account->movements()->exists()) {
                return response()->json(['message' => 'Cannot delete account with transaction history'], 409);
            }

            $account->delete();
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Account not found'], 404);
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
                return response()->json(['message' => 'Cannot change status to CLOSED with active balance'], 409);
            }

            $account->status = $request->status;

            // Set close_date if status is CLOSED
            if ($request->status === 'TUTUP') {
                $account->close_date = now();
            } else {
                $account->close_date = null;
            }

            $account->save();
            return response()->json($account);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Account not found'], 404);
        }
    }
}
