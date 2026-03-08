<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\HolidayPeriod;
use App\Models\HolidayRequirement;
use App\Models\StudentHolidayCheck;
use App\Models\StudentHolidayRequirementStatus;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    public function index()
    {
        $periods = HolidayPeriod::with('requirements')->orderBy('start_date', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $periods
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'requirements' => 'array',
            'requirements.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request) {
            $period = HolidayPeriod::create($request->only(['name', 'start_date', 'end_date', 'description']));

            if ($request->has('requirements')) {
                foreach ($request->requirements as $req) {
                    $period->requirements()->create([
                        'name' => $req['name'],
                        'description' => $req['description'] ?? null
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Periode libur berhasil dibuat',
                'data' => $period->load('requirements')
            ], 201);
        });
    }

    public function show($id)
    {
        $period = HolidayPeriod::with('requirements')->find($id);
        if (!$period) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        return response()->json(['status' => 'success', 'data' => $period]);
    }

    public function update(Request $request, $id)
    {
        $period = HolidayPeriod::find($id);
        if (!$period) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        return DB::transaction(function () use ($request, $period) {
            $period->update($request->only(['name', 'start_date', 'end_date', 'description', 'status']));

            // Simple requirement sync (delete old, create new) or more complex logic
            // For now, let's keep it simple: if requirements provided, update them.
            if ($request->has('requirements')) {
                $period->requirements()->delete();
                foreach ($request->requirements as $req) {
                    $period->requirements()->create([
                        'name' => $req['name'],
                        'description' => $req['description'] ?? null
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Periode libur berhasil diperbarui',
                'data' => $period->load('requirements')
            ]);
        });
    }

    public function destroy($id)
    {
        $period = HolidayPeriod::find($id);
        if (!$period) return response()->json(['message' => 'Data tidak ditemukan'], 404);
        $period->delete();
        return response()->json(['status' => 'success', 'message' => 'Periode libur berhasil dihapus']);
    }

    public function getStudents(Request $request, $id)
    {
        $period = HolidayPeriod::with('requirements')->find($id);
        if (!$period) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        // Get all active students
        $students = Student::where('status', 'active')->get();

        $data = $students->map(function ($student) use ($period) {
            $check = StudentHolidayCheck::where('holiday_period_id', $period->id)
                ->where('student_id', $student->id)
                ->first();

            $requirementStatuses = $period->requirements->map(function ($req) use ($check) {
                $status = $check ? StudentHolidayRequirementStatus::where('student_holiday_check_id', $check->id)
                    ->where('holiday_requirement_id', $req->id)
                    ->first() : null;

                return [
                    'id' => $req->id,
                    'name' => $req->name,
                    'is_met' => $status ? (bool)$status->is_met : false
                ];
            });

            return [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'check' => $check ? [
                    'id' => $check->id,
                    'checkout_at' => $check->checkout_at,
                    'checkin_at' => $checkin_at = $check->checkin_at,
                ] : null,
                'requirements' => $requirementStatuses,
                'is_all_met' => $requirementStatuses->every(fn($r) => $r['is_met'])
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function toggleRequirement(Request $request, $periodId, $studentId, $requirementId)
    {
        return DB::transaction(function () use ($periodId, $studentId, $requirementId) {
            $check = StudentHolidayCheck::firstOrCreate([
                'holiday_period_id' => $periodId,
                'student_id' => $studentId
            ]);

            $status = StudentHolidayRequirementStatus::firstOrNew([
                'student_holiday_check_id' => $check->id,
                'holiday_requirement_id' => $requirementId
            ]);

            $status->is_met = !$status->is_met;
            $status->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Status persyaratan berhasil diperbarui',
                'is_met' => $status->is_met
            ]);
        });
    }

    public function checkout(Request $request, $periodId, $studentId)
    {
        $check = StudentHolidayCheck::where('holiday_period_id', $periodId)
            ->where('student_id', $studentId)
            ->first();

        if (!$check) {
            return response()->json(['message' => 'Persyaratan belum diverifikasi'], 400);
        }

        // Check if all requirements met
        $period = HolidayPeriod::with('requirements')->find($periodId);
        $metCount = StudentHolidayRequirementStatus::where('student_holiday_check_id', $check->id)
            ->where('is_met', true)
            ->count();

        if ($metCount < $period->requirements->count()) {
            return response()->json(['message' => 'Semua persyaratan harus terpenuhi sebelum checkout'], 400);
        }

        $check->update(['checkout_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Check-out berhasil dicatat',
            'checkout_at' => $check->checkout_at
        ]);
    }

    public function checkin(Request $request, $periodId, $studentId)
    {
        $check = StudentHolidayCheck::where('holiday_period_id', $periodId)
            ->where('student_id', $studentId)
            ->first();

        if (!$check || !$check->checkout_at) {
            return response()->json(['message' => 'Santri belum melakukan check-out'], 400);
        }

        $check->update(['checkin_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Check-in berhasil dicatat',
            'checkin_at' => $check->checkin_at
        ]);
    }
}
