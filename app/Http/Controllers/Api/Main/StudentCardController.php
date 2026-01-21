<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentCardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $studentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($studentId)
    {
        $student = Student::findOrFail($studentId);
        $cards = $student->studentCards()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $cards
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Display the specified resource.
     *
     * @param  mixed  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // 1. Try to find by Student ID or NIS
        $student = Student::where('id', $id)->orWhere('nis', $id)->first();
        
        $card = null;

        if ($student) {
            // If student found, get the active card (or latest if no active)
            $card = $student->activeStudentCard ?? $student->studentCards()->latest()->first();
        } 

        // 2. If no card found via Student, try to find by Card ID
        if (!$card) {
            $card = StudentCard::with('student')->find($id);
        }

        if (!$card) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student card not found'
            ], 404);
        }

        // Ensure student is loaded
        if (!$card->relationLoaded('student')) {
            $card->load('student');
        }

        $studentData = [
            'name' => $card->student->first_name . ' ' . $card->student->last_name,
            'birth_place' => $card->student->born_in,
            'birth_date' => $card->student->born_at,
            'address' => $card->student->address,
            'village' => $card->student->village,
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'card' => $card,
                'student_details' => $studentData
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'validator' => 'nullable|string',
            'validation_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $student = Student::findOrFail($request->student_id);

        try {
            DB::beginTransaction();

            // Deactivate all existing active cards
            StudentCard::where('student_id', $student->id)
                ->where('is_active', '=', true)
                ->update(['is_active' => false]);

            // Generate Card Number: NIS-DD-MM-YYYY-RANDOM
            // Example: 12345-21-01-2026-X7Z
            $datePart = now()->format('d-m-Y');
            $randomPart = strtoupper(Str::random(3));
            $cardNumber = sprintf('%s-%s-%s', $student->nis, $datePart, $randomPart);

            // Ensure uniqueness (extremely rare collision chance with random(3) + NIS + Date, but good practice)
            while (StudentCard::where('card_number', '=', $cardNumber)->exists()) {
                $randomPart = strtoupper(Str::random(3));
                $cardNumber = sprintf('%s-%s-%s', $student->nis, $datePart, $randomPart);
            }

            $card = StudentCard::create([
                'student_id' => $student->id,
                'card_number' => $cardNumber,
                'is_active' => true,
                'issued_at' => now(),
                'remarks' => $request->remarks ?? 'New Card Issue',
                'validator' => $request->validator ?? Auth::user()->name ?? 'System',
                'validation_date' => $request->validation_date ?? now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Student card created successfully',
                'data' => $card
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create student card: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate the specified student card.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivate($id)
    {
        $card = StudentCard::findOrFail($id);
        
        $card->update([
            'is_active' => false,
            'remarks' => $card->remarks . ' (Deactivated)',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Student card deactivated successfully',
            'data' => $card
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return $this->deactivate($id);
    }
}
