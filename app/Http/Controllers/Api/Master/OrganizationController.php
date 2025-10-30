<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Http\Resources\OrganizationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Exception;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $organizations = Organization::with(['parent', 'children', 'positions'])->get();
            return new OrganizationResource('Data organisasi berhasil diambil', $organizations, 200);
        } catch (Exception $e) {
            return new OrganizationResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
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
                'description' => 'nullable|string',
                'code' => 'nullable|string|max:50|unique:organizations',
                'parent_id' => 'nullable|exists:organizations,id',
                'level' => 'required|integer|min:1',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return new OrganizationResource('Validasi gagal', $validator->errors(), 422);
            }

            $organization = Organization::create($request->all());
            $organization->load(['parent', 'children']);

            return new OrganizationResource('Organisasi berhasil ditambahkan', $organization, 201);
        } catch (QueryException $e) {
            return new OrganizationResource('Gagal menambahkan organisasi', $e->getMessage(), 500);
        } catch (Exception $e) {
            return new OrganizationResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $organization = Organization::with(['parent', 'children.positions.assignments.official'])->findOrFail($id);
            return new OrganizationResource('Data organisasi berhasil diambil', $organization, 200);
        } catch (Exception $e) {
            return new OrganizationResource('Data organisasi tidak ditemukan', null, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $organization = Organization::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'code' => 'nullable|string|max:50|unique:organizations,code,' . $id,
                'parent_id' => 'nullable|exists:organizations,id',
                'level' => 'required|integer|min:1',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return new OrganizationResource('Validasi gagal', $validator->errors(), 422);
            }

            $organization->update($request->all());
            $organization->load(['parent', 'children']);

            return new OrganizationResource('Organisasi berhasil diperbarui', $organization, 200);
        } catch (Exception $e) {
            return new OrganizationResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $organization = Organization::findOrFail($id);
            $organization->delete();

            return new OrganizationResource('Organisasi berhasil dihapus', null, 200);
        } catch (Exception $e) {
            return new OrganizationResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get root organizations (organizations without parent)
     */
    public function getRootOrganizations()
    {
        try {
            $organizations = Organization::whereNull('parent_id')
                ->with(['children.positions.assignments.official'])
                ->get();

            return new OrganizationResource('Data organisasi berhasil diambil', $organizations, 200);
        } catch (Exception $e) {
            return new OrganizationResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get organization hierarchy
     */
    public function getHierarchy()
    {
        try {
            $organizations = Organization::with('children.children.children.positions.assignments.official')
                ->whereNull('parent_id')
                ->get();

            return new OrganizationResource('Data hierarki organisasi berhasil diambil', $organizations, 200);
        } catch (Exception $e) {
            return new OrganizationResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
}
