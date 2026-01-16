<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassroomRequest;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $classrooms = Classroom::with('classGroups', 'school')->orderByDesc('id')->get();
            return response()->json(new ClassroomResource('Data kelas berhasil diambil', $classrooms, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new ClassroomResource('Gagal mengambil data kelas', null, 500), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClassroomRequest $request): JsonResponse
    {
        try {
            $classroom = Classroom::create($request->validated());
            return response()->json(new ClassroomResource('Kelas berhasil ditambahkan', $classroom, 201), 201);
        } catch (\Exception $e) {
            return response()->json(new ClassroomResource('Gagal menambahkan kelas', null, 500), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $classroom = Classroom::with('classGroups', 'school')->findOrFail($id);
            return response()->json(new ClassroomResource('Data kelas berhasil diambil', $classroom, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new ClassroomResource('Kelas tidak ditemukan', null, 404), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClassroomRequest $request, string $id): JsonResponse
    {
        try {
            $classroom = Classroom::findOrFail($id);
            $classroom->update($request->validated());
            return response()->json(new ClassroomResource('Kelas berhasil diperbarui', $classroom, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new ClassroomResource('Gagal memperbarui kelas', null, 500), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $classroom = Classroom::findOrFail($id);
            $classroom->delete();
            return response()->json(new ClassroomResource('Kelas berhasil dihapus', null, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new ClassroomResource('Gagal menghapus kelas', null, 500), 500);
        }
    }

    /**
     * Export classroom data to Excel (Readable)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ClassroomReadableExport, 'laporan_kelas_' . date('Y-m-d_H-i-s') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Backup classroom data to CSV (Raw)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function backup()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ClassroomBackupExport, 'backup_kelas_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
