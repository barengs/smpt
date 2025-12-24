<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $roles = Role::with('menus')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data role dan menu berhasil diambil',
                'data' => $roles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data role dan menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get menus for a specific role.
     */
    public function getRoleMenus($roleId)
    {
        try {
            $role = Role::with('menus')->findOrFail($roleId);

            return response()->json([
                'status' => 'success',
                'message' => 'Menu untuk role berhasil diambil',
                'data' => $role->menus
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil menu untuk role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get roles for a specific menu.
     */
    public function getMenuRoles($menuId)
    {
        try {
            $menu = Menu::with('roles')->findOrFail($menuId);

            return response()->json([
                'status' => 'success',
                'message' => 'Role untuk menu berhasil diambil',
                'data' => $menu->roles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil role untuk menu: ' . $e->getMessage()
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
                'role_id' => 'required|exists:roles,id',
                'menu_ids' => 'required|array',
                'menu_ids.*' => 'exists:menus,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::findOrFail($request->role_id);
            $role->menus()->sync($request->menu_ids);

            return response()->json([
                'status' => 'success',
                'message' => 'Menu berhasil ditambahkan ke role',
                'data' => $role->load('menus')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan menu ke role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $role = Role::with('menus')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Detail role dan menu berhasil diambil',
                'data' => $role
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail role dan menu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'menu_ids' => 'array',
                'menu_ids.*' => 'exists:menus,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::findOrFail($id);

            if ($request->has('menu_ids')) {
                $role->menus()->sync($request->menu_ids);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Menu untuk role berhasil diperbarui',
                'data' => $role->load('menus')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui menu untuk role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->menus()->detach();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu berhasil dihapus dari role'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus menu dari role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign specific menus to a role.
     */
    public function assignMenuToRole(Request $request, string $roleId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'menu_ids' => 'required|array',
                'menu_ids.*' => 'exists:menus,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::findOrFail($roleId);
            $role->menus()->attach($request->menu_ids);

            return response()->json([
                'status' => 'success',
                'message' => 'Menu berhasil ditambahkan ke role',
                'data' => $role->load('menus')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan menu ke role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get menus accessible by the authenticated user
     */
    public function getUserMenus(Request $request)
    {
        try {
            $user = $request->user();
            $menus = $user->getAccessibleMenus();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu yang dapat diakses berhasil diambil',
                'data' => $menus
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil menu yang dapat diakses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove specific menus from a role.
     */
    public function removeMenuFromRole(Request $request, string $roleId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'menu_ids' => 'required|array',
                'menu_ids.*' => 'exists:menus,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::findOrFail($roleId);
            $role->menus()->detach($request->menu_ids);

            return response()->json([
                'status' => 'success',
                'message' => 'Menu berhasil dihapus dari role'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus menu dari role: ' . $e->getMessage()
            ], 500);
        }
    }
}
