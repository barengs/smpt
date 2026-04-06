<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentAgreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class StudentAgreementController extends Controller
{
    /**
     * Get agreement status for a student
     */
    public function index($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            
            $agreement = StudentAgreement::firstOrCreate(
                ['student_id' => $student->id],
                ['doc_number' => StudentAgreement::generateDocNumber()]
            );

            return response()->json([
                'success' => true,
                'data' => $agreement,
                'student' => $student->load(['parents'])
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data perjanjian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save agreement step
     */
    public function updateStep(Request $request, $studentId)
    {
        $request->validate([
            'step' => 'required|in:contract,compliance,urine_test',
            'agreed' => 'required|boolean',
            'contract_level' => 'nullable|string|in:ULA,WUSTHO,ULYA,TUGAS',
        ]);

        try {
            DB::beginTransaction();

            $agreement = StudentAgreement::where('student_id', $studentId)->firstOrFail();
            
            $step = $request->step;
            $agreed = $request->agreed;
            
            $updateData = [];
            
            if ($step === 'contract') {
                $updateData['contract_agreed'] = $agreed;
                $updateData['contract_level'] = $request->contract_level;
                $updateData['contract_agreed_at'] = $agreed ? now() : null;
            } elseif ($step === 'compliance') {
                $updateData['compliance_agreed'] = $agreed;
                $updateData['compliance_agreed_at'] = $agreed ? now() : null;
            } elseif ($step === 'urine_test') {
                $updateData['urine_test_agreed'] = $agreed;
                $updateData['urine_test_agreed_at'] = $agreed ? now() : null;
            }

            $agreement->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progres perjanjian berhasil disimpan',
                'data' => $agreement
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan progres perjanjian',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a list of students with their agreement status
     */
    public function listAgreements(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');

        $query = Student::with(['agreement', 'program'])
            ->when($search, function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });

        $students = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }
}
