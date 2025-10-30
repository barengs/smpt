<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Http\Resources\PositionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Exception;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $positions = Position::with(['organization', 'parent'])->get();
            return new PositionResource('Data jabatan berhasil diambil', $positions, 200);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
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
                'code' => 'required|string|max:50|unique:positions',
                'description' => 'nullable|string',
                'organization_id' => 'required|exists:organizations,id',
                'parent_id' => 'nullable|exists:positions,id',
                'level' => 'required|integer|min:1',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return new PositionResource('Validasi gagal', $validator->errors(), 422);
            }

            $position = Position::create($request->all());
            $position->load(['organization', 'parent']);

            return new PositionResource('Jabatan berhasil ditambahkan', $position, 201);
        } catch (QueryException $e) {
            return new PositionResource('Gagal menambahkan jabatan', $e->getMessage(), 500);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $position = Position::with(['organization', 'parent', 'children', 'assignments.official'])->findOrFail($id);
            return new PositionResource('Data jabatan berhasil diambil', $position, 200);
        } catch (Exception $e) {
            return new PositionResource('Data jabatan tidak ditemukan', null, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $position = Position::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:positions,code,' . $id,
                'description' => 'nullable|string',
                'organization_id' => 'required|exists:organizations,id',
                'parent_id' => 'nullable|exists:positions,id',
                'level' => 'required|integer|min:1',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return new PositionResource('Validasi gagal', $validator->errors(), 422);
            }

            $position->update($request->all());
            $position->load(['organization', 'parent']);

            return new PositionResource('Jabatan berhasil diperbarui', $position, 200);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $position = Position::findOrFail($id);
            $position->delete();

            return new PositionResource('Jabatan berhasil dihapus', null, 200);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get positions by organization
     */
    public function getByOrganization($organizationId)
    {
        try {
            $positions = Position::where('organization_id', $organizationId)
                ->with(['organization', 'parent'])
                ->get();

            return new PositionResource('Data jabatan berhasil diambil', $positions, 200);
        } catch (Exception $e) {
            return new PositionResource('Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
}
