<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $permissions = Permission::with('roles')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data izin berhasil diambil',
                'data' => $permissions
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data izin: ' . $e->getMessage()
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
                'name' => 'required|string|unique:permissions,name',
                'guard_name' => 'required|string|in:api,web'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $permission = Permission::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Izin berhasil dibuat',
                'data' => $permission
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat izin: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $permission = Permission::with('roles')->find($id);

            if (!$permission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Izin tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data izin berhasil diambil',
                'data' => $permission
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data izin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $permission = Permission::find($id);

            if (!$permission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Izin tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|unique:permissions,name,' . $id,
                'guard_name' => 'sometimes|required|string|in:api,web'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update permission if name or guard_name is provided
            if ($request->has('name') || $request->has('guard_name')) {
                $updateData = [];
                if ($request->has('name')) {
                    $updateData['name'] = $request->name;
                }
                if ($request->has('guard_name')) {
                    $updateData['guard_name'] = $request->guard_name;
                }
                $permission->update($updateData);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Izin berhasil diperbarui',
                'data' => $permission
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui izin: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $permission = Permission::find($id);

            if (!$permission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Izin tidak ditemukan'
                ], 404);
            }

            $permission->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Izin berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus izin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign roles to a permission.
     */
    public function assignRoles(Request $request, string $id)
    {
        try {
            $permission = Permission::find($id);

            if (!$permission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Izin tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $roles = Role::whereIn('name', $request->roles)->get();
            $permission->assignRole($roles);

            // Load roles for response
            $permission->load('roles');

            return response()->json([
                'status' => 'success',
                'message' => 'Peran berhasil ditetapkan ke izin',
                'data' => $permission
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menetapkan peran ke izin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove roles from a permission.
     */
    public function removeRoles(Request $request, string $id)
    {
        try {
            $permission = Permission::find($id);

            if (!$permission) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Izin tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $roles = Role::whereIn('name', $request->roles)->get();
            $permission->removeRole($roles);

            // Load roles for response
            $permission->load('roles');

            return response()->json([
                'status' => 'success',
                'message' => 'Peran berhasil dihapus dari izin',
                'data' => $permission
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus peran dari izin: ' . $e->getMessage()
            ], 500);
        }
    }
}
