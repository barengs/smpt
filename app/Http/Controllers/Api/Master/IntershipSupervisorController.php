<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\InternshipSupervisorRequest;
use App\Http\Resources\InternshipSupervisorResource;
use App\Models\InternshipSupervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntershipSupervisorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $internshipSupervisors = InternshipSupervisor::all();
            return new InternshipSupervisorResource('Data supervisor magang berhasil diambil', $internshipSupervisors, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving internship supervisors: ' . $e->getMessage());
            return new InternshipSupervisorResource('Gagal mengambil data supervisor magang', null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InternshipSupervisorRequest $request)
    {
        try {
            $internshipSupervisor = InternshipSupervisor::create($request->validated());
            return new InternshipSupervisorResource('Supervisor magang berhasil ditambahkan', $internshipSupervisor, 201);
        } catch (\Exception $e) {
            Log::error('Error creating internship supervisor: ' . $e->getMessage());
            return new InternshipSupervisorResource('Gagal menambahkan supervisor magang', null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $internshipSupervisor = InternshipSupervisor::findOrFail($id);
            return new InternshipSupervisorResource('Data supervisor magang berhasil diambil', $internshipSupervisor, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new InternshipSupervisorResource('Supervisor magang tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving internship supervisor: ' . $e->getMessage());
            return new InternshipSupervisorResource('Gagal mengambil data supervisor magang', null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InternshipSupervisorRequest $request, string $id)
    {
        try {
            $internshipSupervisor = InternshipSupervisor::findOrFail($id);
            $internshipSupervisor->update($request->validated());
            return new InternshipSupervisorResource('Supervisor magang berhasil diperbarui', $internshipSupervisor, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new InternshipSupervisorResource('Supervisor magang tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error updating internship supervisor: ' . $e->getMessage());
            return new InternshipSupervisorResource('Gagal memperbarui supervisor magang', null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $internshipSupervisor = InternshipSupervisor::findOrFail($id);
            $internshipSupervisor->delete();
            return new InternshipSupervisorResource('Supervisor magang berhasil dihapus', null, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new InternshipSupervisorResource('Supervisor magang tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error deleting internship supervisor: ' . $e->getMessage());
            return new InternshipSupervisorResource('Gagal menghapus supervisor magang', null, 500);
        }
    }

    /**
     * Display a listing of the trashed resource.
     */
    public function trashed()
    {
        try {
            $trashedInternshipSupervisors = InternshipSupervisor::onlyTrashed()->get();
            return new InternshipSupervisorResource('Data supervisor magang yang dihapus berhasil diambil', $trashedInternshipSupervisors, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving trashed internship supervisors: ' . $e->getMessage());
            return new InternshipSupervisorResource('Gagal mengambil data supervisor magang yang dihapus', null, 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $internshipSupervisor = InternshipSupervisor::onlyTrashed()->findOrFail($id);
            $internshipSupervisor->restore();
            return new InternshipSupervisorResource('Supervisor magang berhasil dipulihkan', $internshipSupervisor, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new InternshipSupervisorResource('Supervisor magang yang dihapus tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error restoring internship supervisor: ' . $e->getMessage());
            return new InternshipSupervisorResource('Gagal memulihkan supervisor magang', null, 500);
        }
    }
}
