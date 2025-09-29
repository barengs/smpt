<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Http\Requests\EducationalInstitutionRequest;
use App\Http\Resources\EducationalInstitutionResource;
use App\Models\EducationalInstitution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EducationalInstitutionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $educationalInstitutions = EducationalInstitution::with(['education', 'educationClass', 'headmaster'])->get();
            return new EducationalInstitutionResource('Data institusi pendidikan berhasil diambil', $educationalInstitutions, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving educational institutions: ' . $e->getMessage());
            return new EducationalInstitutionResource('Gagal mengambil data institusi pendidikan', null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EducationalInstitutionRequest $request)
    {
        try {
            DB::beginTransaction();

            $educationalInstitution = EducationalInstitution::create($request->validated());

            DB::commit();
            return new EducationalInstitutionResource('Institusi pendidikan berhasil ditambahkan', $educationalInstitution, 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating educational institution: ' . $e->getMessage());
            return new EducationalInstitutionResource('Gagal menambahkan institusi pendidikan', null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $educationalInstitution = EducationalInstitution::with(['education', 'educationClass', 'headmaster'])->findOrFail($id);
            return new EducationalInstitutionResource('Data institusi pendidikan berhasil diambil', $educationalInstitution, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new EducationalInstitutionResource('Institusi pendidikan tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving educational institution: ' . $e->getMessage());
            return new EducationalInstitutionResource('Gagal mengambil data institusi pendidikan', null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EducationalInstitutionRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $educationalInstitution = EducationalInstitution::findOrFail($id);
            $educationalInstitution->update($request->validated());

            DB::commit();
            return new EducationalInstitutionResource('Institusi pendidikan berhasil diperbarui', $educationalInstitution, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new EducationalInstitutionResource('Institusi pendidikan tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating educational institution: ' . $e->getMessage());
            return new EducationalInstitutionResource('Gagal memperbarui institusi pendidikan', null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $educationalInstitution = EducationalInstitution::findOrFail($id);
            $educationalInstitution->delete();

            DB::commit();
            return new EducationalInstitutionResource('Institusi pendidikan berhasil dihapus', null, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new EducationalInstitutionResource('Institusi pendidikan tidak ditemukan', null, 404);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting educational institution: ' . $e->getMessage());
            return new EducationalInstitutionResource('Gagal menghapus institusi pendidikan', null, 500);
        }
    }
}
