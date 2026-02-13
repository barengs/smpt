<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Exception;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $roles = Role::with(['permissions', 'menus'])->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data peran berhasil diambil',
                'data' => $roles
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data peran: ' . $e->getMessage()
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
                'name' => 'required|string|unique:roles,name',
                'guard_name' => 'required|string|in:api,web',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name
            ]);

            // Assign permissions if provided
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('name', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            // Load permissions for response
            $role->load('permissions');

            return response()->json([
                'status' => 'success',
                'message' => 'Peran berhasil dibuat',
                'data' => $role
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat peran: ' . $e->getMessage()
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
            $role = Role::with(['permissions', 'menus'])->find($id);

            if (!$role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peran tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data peran berhasil diambil',
                'data' => $role
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data peran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peran tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|unique:roles,name,' . $id,
                'guard_name' => 'sometimes|required|string|in:api,web',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update role if name or guard_name is provided
            if ($request->has('name') || $request->has('guard_name')) {
                $updateData = [];
                if ($request->has('name')) {
                    $updateData['name'] = $request->name;
                }
                if ($request->has('guard_name')) {
                    $updateData['guard_name'] = $request->guard_name;
                }
                $role->update($updateData);
            }

            // Sync permissions if provided
            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('name', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            // Load permissions for response
            $role->load('permissions');

            return response()->json([
                'status' => 'success',
                'message' => 'Peran berhasil diperbarui',
                'data' => $role
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui peran: ' . $e->getMessage()
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
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peran tidak ditemukan'
                ], 404);
            }

            $role->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Peran berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus peran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to a role.
     */
    public function assignPermissions(Request $request, string $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peran tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $permissions = Permission::whereIn('name', $request->permissions)->get();
            $role->givePermissionTo($permissions);

            // Load permissions for response
            $role->load('permissions');

            return response()->json([
                'status' => 'success',
                'message' => 'Izin berhasil ditetapkan ke peran',
                'data' => $role
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menetapkan izin ke peran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove permissions from a role.
     */
    public function removePermissions(Request $request, string $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peran tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $permissions = Permission::whereIn('name', $request->permissions)->get();
            $role->revokePermissionTo($permissions);

            // Load permissions for response
            $role->load('permissions');

            return response()->json([
                'status' => 'success',
                'message' => 'Izin berhasil dihapus dari peran',
                'data' => $role
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus izin dari peran: ' . $e->getMessage()
            ], 500);
        }
    }
}
