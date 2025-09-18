<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Http\Resources\PermissionResource;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $permission = Permission::all();
            return new PermissionResource('success', $permission, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to retrieve permission',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'guard_name' => 'nullable|string',
            ]);

            $permission = Permission::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'api',
            ]);
            return new PermissionResource('success', $permission, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to create permission',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $permission = Permission::findOrFail($id);

            $data = $request->validate([
                'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
                'guard_name' => 'nullable|string',
            ]);

            $permission->update([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'api',
            ]);

            return new PermissionResource('success', $permission, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Failed to update permission',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
