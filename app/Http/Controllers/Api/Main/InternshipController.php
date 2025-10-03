<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Internship;
use App\Http\Requests\InternshipRequest;
use App\Http\Resources\InternshipResource;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class InternshipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Internship::with(['academicYear', 'student', 'supervisor']);

            // Filter by academic year if provided
            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            // Filter by student if provided
            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            // Filter by supervisor if provided
            if ($request->has('supervisor_id')) {
                $query->where('supervisor_id', $request->supervisor_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $internships = $query->paginate($request->get('per_page', 15));

            return new InternshipResource('Data magang berhasil diambil', $internships, 200);
        } catch (QueryException $e) {
            Log::error('Database error while fetching internships: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan database saat mengambil data magang', null, 500);
        } catch (Exception $e) {
            Log::error('Error while fetching internships: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan saat mengambil data magang', null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InternshipRequest $request)
    {
        try {
            $internship = Internship::create($request->validated());

            // Load relationships
            $internship->load(['academicYear', 'student', 'supervisor']);

            return new InternshipResource('Data magang berhasil disimpan', $internship, 201);
        } catch (ValidationException $e) {
            return new InternshipResource('Validasi gagal', $e->errors(), 422);
        } catch (QueryException $e) {
            Log::error('Database error while creating internship: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan database saat menyimpan data magang', null, 500);
        } catch (Exception $e) {
            Log::error('Error while creating internship: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan saat menyimpan data magang', null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $internship = Internship::with(['academicYear', 'student', 'supervisor'])->findOrFail($id);

            return new InternshipResource('Data magang berhasil diambil', $internship, 200);
        } catch (QueryException $e) {
            Log::error('Database error while fetching internship: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan database saat mengambil data magang', null, 500);
        } catch (Exception $e) {
            Log::error('Error while fetching internship: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan saat mengambil data magang', null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InternshipRequest $request, string $id)
    {
        try {
            $internship = Internship::findOrFail($id);

            $internship->update($request->validated());

            // Load relationships
            $internship->load(['academicYear', 'student', 'supervisor']);

            return new InternshipResource('Data magang berhasil diperbarui', $internship, 200);
        } catch (ValidationException $e) {
            return new InternshipResource('Validasi gagal', $e->errors(), 422);
        } catch (QueryException $e) {
            Log::error('Database error while updating internship: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan database saat memperbarui data magang', null, 500);
        } catch (Exception $e) {
            Log::error('Error while updating internship: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan saat memperbarui data magang', null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $internship = Internship::findOrFail($id);
            $internship->delete();

            return new InternshipResource('Data magang berhasil dihapus', null, 200);
        } catch (QueryException $e) {
            Log::error('Database error while deleting internship: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan database saat menghapus data magang', null, 500);
        } catch (Exception $e) {
            Log::error('Error while deleting internship: ' . $e->getMessage());
            return new InternshipResource('Terjadi kesalahan saat menghapus data magang', null, 500);
        }
    }
}
