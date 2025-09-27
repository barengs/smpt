<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYearRequest;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $academicYears = AcademicYear::all();
            return new AcademicYearResource('Data tahun ajaran berhasil diambil', $academicYears, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving academic years: ' . $e->getMessage());
            return new AcademicYearResource('Gagal mengambil data tahun ajaran', null, 500);
        }
    }

    /**
     * Display the specified resource where active = true.
     */
    public function showActiveAcademic()
    {
        try {
            $academicYear = AcademicYear::where('active', '=', 1)->first();
            if (!$academicYear) {
                return new AcademicYearResource('Tidak ada tahun ajaran yang aktif', null, 404);
            }
            return new AcademicYearResource('Data tahun ajaran berhasil diambil', $academicYear, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving active academic year: ' . $e->getMessage());
            return new AcademicYearResource('Gagal mengambil data tahun ajaran yang aktif', null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AcademicYearRequest $request)
    {
        try {
            if ($request->type == 'semester'){
                $request->validated([
                    'periode' =>  'required|enum:ganjil,genap'
                ]);
            }
            $academicYear = AcademicYear::create($request->validated());
            // jika berhasil update data lainnya
            // nonaktifkan tahun ajaran yang lain
            if ($academicYear) {
                AcademicYear::where('id', '!=', $academicYear->id)->update([
                    'active' => false
                ]);
            }
            return new AcademicYearResource('Tahun ajaran berhasil ditambahkan', $academicYear, 201);
        } catch (\Exception $e) {
            Log::error('Error creating academic year: ' . $e->getMessage());
            return new AcademicYearResource('Gagal menambahkan tahun ajaran', null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $academicYear = AcademicYear::findOrFail($id);
            return new AcademicYearResource('Data tahun ajaran berhasil diambil', $academicYear, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new AcademicYearResource('Tahun ajaran tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving academic year: ' . $e->getMessage());
            return new AcademicYearResource('Gagal mengambil data tahun ajaran', null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AcademicYearRequest $request, string $id)
    {
        try {
            $academicYear = AcademicYear::findOrFail($id);
            $academicYear->update($request->validated());
            return new AcademicYearResource('Tahun ajaran berhasil diperbarui', $academicYear, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new AcademicYearResource('Tahun ajaran tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error updating academic year: ' . $e->getMessage());
            return new AcademicYearResource('Gagal memperbarui tahun ajaran', null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $academicYear = AcademicYear::findOrFail($id);
            $academicYear->delete();
            return new AcademicYearResource('Tahun ajaran berhasil dihapus', null, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new AcademicYearResource('Tahun ajaran tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error deleting academic year: ' . $e->getMessage());
            return new AcademicYearResource('Gagal menghapus tahun ajaran', null, 500);
        }
    }

    /**
     * Display a listing of the trashed resource.
     */
    public function trashed()
    {
        try {
            $trashedAcademicYears = AcademicYear::onlyTrashed()->get();
            return new AcademicYearResource('Data tahun ajaran yang dihapus berhasil diambil', $trashedAcademicYears, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving trashed academic years: ' . $e->getMessage());
            return new AcademicYearResource('Gagal mengambil data tahun ajaran yang dihapus', null, 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $academicYear = AcademicYear::onlyTrashed()->findOrFail($id);
            $academicYear->restore();
            return new AcademicYearResource('Tahun ajaran berhasil dipulihkan', $academicYear, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new AcademicYearResource('Tahun ajaran yang dihapus tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error restoring academic year: ' . $e->getMessage());
            return new AcademicYearResource('Gagal memulihkan tahun ajaran', null, 500);
        }
    }
}
