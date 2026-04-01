<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicQuarterRequest;
use App\Http\Resources\AcademicQuarterResource;
use App\Models\AcademicQuarter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AcademicQuarterController extends Controller
{
    /**
     * Display a listing of quarters, optionally filtered by academic_year_id.
     */
    public function index(Request $request)
    {
        try {
            $query = AcademicQuarter::query();
            
            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            $quarters = $query->with('academicYear')->get();
            return new AcademicQuarterResource('Data kuartal berhasil diambil', $quarters, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving quarters: ' . $e->getMessage());
            return new AcademicQuarterResource('Gagal mengambil data kuartal', null, 500);
        }
    }

    /**
     * Display active quarter.
     */
    public function showActiveQuarter()
    {
        try {
            $quarter = AcademicQuarter::where('active', 1)->first();
            if (!$quarter) {
                return new AcademicQuarterResource('Tidak ada kuartal yang aktif', null, 404);
            }
            return new AcademicQuarterResource('Data kuartal berhasil diambil', $quarter, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving active quarter: ' . $e->getMessage());
            return new AcademicQuarterResource('Gagal mengambil data kuartal yang aktif', null, 500);
        }
    }

    /**
     * Store a newly created quarter.
     */
    public function store(AcademicQuarterRequest $request)
    {
        try {
            $quarter = AcademicQuarter::create($request->validated());
            if ($quarter->active) {
                AcademicQuarter::where('id', '!=', $quarter->id)->update(['active' => false]);
            }
            return new AcademicQuarterResource('Kuartal berhasil ditambahkan', $quarter, 201);
        } catch (\Exception $e) {
            Log::error('Error creating quarter: ' . $e->getMessage());
            return new AcademicQuarterResource('Gagal menambahkan kuartal', null, 500);
        }
    }

    /**
     * Display the specified quarter.
     */
    public function show($id)
    {
        try {
            $quarter = AcademicQuarter::with('academicYear')->findOrFail($id);
            return new AcademicQuarterResource('Data kuartal berhasil diambil', $quarter, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new AcademicQuarterResource('Kuartal tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving quarter: ' . $e->getMessage());
            return new AcademicQuarterResource('Gagal mengambil data kuartal', null, 500);
        }
    }

    /**
     * Update the specified quarter.
     */
    public function update(AcademicQuarterRequest $request, $id)
    {
        try {
            $quarter = AcademicQuarter::findOrFail($id);
            $quarter->update($request->validated());
            if ($quarter->active) {
                AcademicQuarter::where('id', '!=', $quarter->id)->update(['active' => false]);
            }
            return new AcademicQuarterResource('Kuartal berhasil diperbarui', $quarter, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new AcademicQuarterResource('Kuartal tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error updating quarter: ' . $e->getMessage());
            return new AcademicQuarterResource('Gagal memperbarui kuartal', null, 500);
        }
    }

    /**
     * Remove the specified quarter.
     */
    public function destroy($id)
    {
        try {
            $quarter = AcademicQuarter::findOrFail($id);
            $quarter->delete();
            return new AcademicQuarterResource('Kuartal berhasil dihapus', null, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new AcademicQuarterResource('Kuartal tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error deleting quarter: ' . $e->getMessage());
            return new AcademicQuarterResource('Gagal menghapus kuartal', null, 500);
        }
    }
}
