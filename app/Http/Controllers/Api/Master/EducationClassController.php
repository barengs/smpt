<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\EducationClassRequest;
use App\Http\Resources\EducationClassResource;
use App\Models\EducationClass;
use Illuminate\Http\JsonResponse;

class EducationClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $educationClasses = EducationClass::with('education')->orderByDesc('id')->get();
            return response()->json(new EducationClassResource('Data kelas pendidikan berhasil diambil', $educationClasses, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new EducationClassResource('Gagal mengambil data kelas pendidikan', null, 500), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EducationClassRequest $request): JsonResponse
    {
        try {
            $educationClass = EducationClass::create($request->only(['code', 'name']));

            // Sync educations if provided
            if ($request->has('education_ids')) {
                $educationClass->education()->sync($request->education_ids);
            }

            // Load the education relationship
            $educationClass->load('education');

            return response()->json(new EducationClassResource('Kelas pendidikan berhasil ditambahkan', $educationClass, 201), 201);
        } catch (\Exception $e) {
            return response()->json(new EducationClassResource('Gagal menambahkan kelas pendidikan', null, 500), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $educationClass = EducationClass::with('education')->findOrFail($id);
            return response()->json(new EducationClassResource('Data kelas pendidikan berhasil diambil', $educationClass, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new EducationClassResource('Kelas pendidikan tidak ditemukan', null, 404), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EducationClassRequest $request, string $id): JsonResponse
    {
        try {
            $educationClass = EducationClass::findOrFail($id);
            $educationClass->update($request->only(['code', 'name']));

            // Sync educations if provided
            if ($request->has('education_ids')) {
                $educationClass->education()->sync($request->education_ids);
            }

            // Load the education relationship
            $educationClass->load('education');

            return response()->json(new EducationClassResource('Kelas pendidikan berhasil diperbarui', $educationClass, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new EducationClassResource('Gagal memperbarui kelas pendidikan', null, 500), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $educationClass = EducationClass::findOrFail($id);
            $educationClass->delete();
            return response()->json(new EducationClassResource('Kelas pendidikan berhasil dihapus', null, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new EducationClassResource('Gagal menghapus kelas pendidikan', null, 500), 500);
        }
    }

    /**
     * Export education class data to Excel (Readable)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EducationClassReadableExport, 'laporan_kelompok_pendidikan_' . date('Y-m-d_H-i-s') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Backup education class data to CSV (Raw)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function backup()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\EducationClassBackupExport, 'backup_kelompok_pendidikan_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
