<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Classroom;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ClassGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $classGroups = ClassGroup::with(['classroom', 'advisor.user', 'educational_institution:id,institution_name'])->orderByDesc('id')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data kelompok kelas berhasil diambil',
                'data' => $classGroups
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'classroom_id' => 'required|exists:classrooms,id',
                'advisor_id' => 'nullable|exists:staff,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if the staff member has the 'walikelas' role
            if ($request->advisor_id) {
                $staff = Staff::with('user')->find($request->advisor_id);
                if (!$staff || !$staff->user || !$staff->user->hasRole('walikelas')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Staff yang dipilih bukan memiliki role walikelas'
                    ], 422);
                }
            }

            $classGroup = ClassGroup::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kelompok kelas berhasil ditambahkan',
                'data' => $classGroup->load(['classroom', 'advisor.user'])
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $classGroup = ClassGroup::with(['classroom', 'advisor.user', 'educational_institution:id,institution_name'])->find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data kelompok kelas berhasil diambil',
                'data' => $classGroup
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $classGroup = ClassGroup::find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'classroom_id' => 'required|exists:classrooms,id',
                'advisor_id' => 'nullable|exists:staff,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if the staff member has the 'walikelas' role
            if ($request->advisor_id) {
                $staff = Staff::with('user')->find($request->advisor_id);
                if (!$staff || !$staff->user || !$staff->user->hasRole('walikelas')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Staff yang dipilih bukan memiliki role walikelas'
                    ], 422);
                }
            }

            $classGroup->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kelompok kelas berhasil diperbarui',
                'data' => $classGroup->load(['classroom', 'advisor.user'])
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $classGroup = ClassGroup::find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            $classGroup->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kelompok kelas berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $classGroup = ClassGroup::withTrashed()->find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            if (!$classGroup->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak dalam keadaan terhapus'
                ], 400);
            }

            $classGroup->restore();

            return response()->json([
                'success' => true,
                'message' => 'Kelompok kelas berhasil dipulihkan',
                'data' => $classGroup->load(['classroom', 'advisor.user'])
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan kelompok kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the trashed resource.
     */
    public function trashed()
    {
        try {
            $classGroups = ClassGroup::with(['classroom', 'advisor.user'])->onlyTrashed()->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data kelompok kelas terhapus berhasil diambil',
                'data' => $classGroups
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kelompok kelas terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all staff members with 'walikelas' role
     */
    public function getAdvisors()
    {
        try {
            $advisors = Staff::whereHas('user', function ($query) {
                $query->role('walikelas');
            })->with('user')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data wali kelas berhasil diambil',
                'data' => $advisors
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data wali kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign or change advisor for a class group
     */
    public function assignAdvisor(Request $request, string $id)
    {
        try {
            $classGroup = ClassGroup::find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'advisor_id' => 'required|exists:staff,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if the staff member has the 'walikelas' role
            $staff = Staff::with('user')->find($request->advisor_id);
            if (!$staff || !$staff->user || !$staff->user->hasRole('walikelas')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff yang dipilih bukan memiliki role walikelas'
                ], 422);
            }

            $classGroup->update(['advisor_id' => $request->advisor_id]);

            return response()->json([
                'success' => true,
                'message' => 'Wali kelas berhasil ditetapkan',
                'data' => $classGroup->load(['classroom', 'advisor.user'])
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan wali kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove advisor from a class group
     */
    public function removeAdvisor(string $id)
    {
        try {
            $classGroup = ClassGroup::find($id);

            if (!$classGroup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelompok kelas tidak ditemukan'
                ], 404);
            }

            $classGroup->update(['advisor_id' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Wali kelas berhasil dihapus',
                'data' => $classGroup->load(['classroom', 'advisor.user'])
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus wali kelas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get class groups with classroom, educational institution, and student count
     */
    public function getClassGroupsWithDetails(Request $request)
    {
        try {
            $query = ClassGroup::with([
                'classroom:id,name',
                'educational_institution:id,institution_name',
                'advisor.user:id,name'
            ])
            ->withCount(['studentClasses as total_students']);

            // Filter by academic year if provided
            if ($request->has('academic_year_id')) {
                $query->whereHas('studentClasses', function ($q) use ($request) {
                    $q->where('academic_year_id', $request->academic_year_id);
                });
            }

            // Filter by educational institution if provided
            if ($request->has('educational_institution_id')) {
                $query->where('educational_institution_id', $request->educational_institution_id);
            }

            // Filter by classroom if provided
            if ($request->has('classroom_id')) {
                $query->where('classroom_id', $request->classroom_id);
            }

            $classGroups = $query->orderByDesc('id')->get();

            // Transform the data to include student count
            $data = $classGroups->map(function ($classGroup) {
                return [
                    'id' => $classGroup->id,
                    'name' => $classGroup->name,
                    'classroom' => $classGroup->classroom,
                    'educational_institution' => $classGroup->educational_institution,
                    'advisor' => $classGroup->advisor ? [
                        'id' => $classGroup->advisor->id,
                        'user' => $classGroup->advisor->user
                    ] : null,
                    'total_students' => $classGroup->total_students ?? 0,
                    'created_at' => $classGroup->created_at,
                    'updated_at' => $classGroup->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data rombongan belajar berhasil diambil',
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data rombongan belajar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
