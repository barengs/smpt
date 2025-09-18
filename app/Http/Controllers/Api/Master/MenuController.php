<?php

namespace App\Http\Controllers\Api\Master;

use App\Models\Menu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Resources\MenuPermissionResource;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Menu::with(['child'])->get();

            return new MenuResource('success', $data, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_title' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:menus,id',
        ]);

        try {
            $maxOrder = Menu::max('order') ?? 0;
            $menu = Menu::create([
                'id_title' => $request->title,
                'en_title' => $request->en_title,
                'ar_title' => $request->ar_title,
                'description' => $request->description,
                'icon' => $request->icon,
                'route' => $request->route,
                'parent_id' => $request->parent_id,
                'type' => $request->type ?? 'link',
                'position' => $request->position ?? 'side',
                'status' => $request->status ?? 'active',
                'order' => $maxOrder + 1,
            ]);

            return new MenuResource('Menu created successfully', $menu, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create menu',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $menu = Menu::with(['child'])->findOrFail($id);

            return new MenuResource('success', $menu, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Menu not found',
                'error' => $th->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $request->validate([
            'id_title' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:menus,id',
        ]);

        try {
            $menu = Menu::findOrFail($id);
            $menu->update([
                'id_title' => $request->title,
                'en_title' => $request->en_title,
                'ar_title' => $request->ar_title,
                'description' => $request->description,
                'icon' => $request->icon,
                'route' => $request->route,
                'parent_id' => $request->parent_id,
                'type' => $request->type ?? $menu->type,
                'position' => $request->position ?? $menu->position,
                'status' => $request->status ?? $menu->status,
                'order' => $request->order ?? $menu->order,
            ]);

            return new MenuResource('Menu updated successfully', $menu, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update menu',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $menu = Menu::findOrFail($id);

            // Delete related permissions first
            $menu->permissions()->detach();

            // Delete the menu
            $menu->delete();

            return new MenuResource('Menu deleted successfully', null, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to delete menu',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign permissions to a menu
     *
     * @param Request $request
     * @param string $menuId
     * @return \App\Http\Resources\MenuPermissionResource
     */
    public function assignMenuPermission(Request $request, string $menuId)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return new MenuPermissionResource('Validation failed', $validator->errors(), 422);
            }

            // Find the menu
            $menu = Menu::findOrFail($menuId);

            // Sync permissions to the menu
            $menu->permissions()->sync($request->permissions);

            // Load the permissions relationship
            $menu->load('permissions');

            return new MenuPermissionResource('Permissions assigned to menu successfully', $menu, 200);
        } catch (\Throwable $th) {
            return new MenuPermissionResource('Failed to assign permissions to menu', ['error' => $th->getMessage()], 500);
        }
    }

    /**
     * Get permissions assigned to a menu
     *
     * @param string $menuId
     * @return \App\Http\Resources\MenuPermissionResource
     */
    public function getMenuPermissions(string $menuId)
    {
        try {
            // Find the menu with its permissions
            $menu = Menu::with('permissions')->findOrFail($menuId);

            return new MenuPermissionResource('Menu permissions retrieved successfully', $menu->permissions, 200);
        } catch (\Throwable $th) {
            return new MenuPermissionResource('Failed to retrieve menu permissions', ['error' => $th->getMessage()], 500);
        }
    }

    /**
     * Remove specific permissions from a menu
     *
     * @param Request $request
     * @param string $menuId
     * @return \App\Http\Resources\MenuPermissionResource
     */
    public function removeMenuPermission(Request $request, string $menuId)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return new MenuPermissionResource('Validation failed', $validator->errors(), 422);
            }

            // Find the menu
            $menu = Menu::findOrFail($menuId);

            // Detach specified permissions from the menu
            $menu->permissions()->detach($request->permissions);

            return new MenuPermissionResource('Permissions removed from menu successfully', null, 200);
        } catch (\Throwable $th) {
            return new MenuPermissionResource('Failed to remove permissions from menu', ['error' => $th->getMessage()], 500);
        }
    }
}
