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
    /**
     * Sync Role Access (Menus and Permissions) in one go.
     * "Concept-Based Access Control"
     */
    public function syncRoleAccess(Request $request, string $roleId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array',
                'items.*.menu_id' => 'required|exists:menus,id',
                'items.*.permissions' => 'array', // e.g., ['view', 'create', 'edit', 'delete', 'approve']
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::findOrFail($roleId);

            \Illuminate\Support\Facades\DB::beginTransaction();

            $menuIds = collect($request->items)->pluck('menu_id')->toArray();
            
            // 1. Sync Menus
            $role->menus()->sync($menuIds);

            // 2. Sync Permissions
            $allPermissionIds = [];
            
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
                // Use English title for permission slug, fallback to ID title if needed
                $menuSlug = \Illuminate\Support\Str::slug($menu->en_title ?? $menu->id_title); 

                // If permissions array is empty, default to just 'view' or nothing? 
                // Let's assume passed permissions are what they get.
                $actions = $item['permissions'] ?? [];
                
                // Always ensure they have 'view' if they have the menu? 
                // Let's stick to what's requested.
                
                foreach ($actions as $action) {
                    $permissionName = "{$action} {$menuSlug}"; // e.g., "create student-data", "view dashboard"
                    // Or keep existing format: "buat santri" etc.
                    // The existing seeder uses Indonesian: "buat santri", "lihat santri".
                    // We need to match EXISTING convention if possible, OR switch to dynamic English.
                    // IMPORTANT: The user said "simplify". Using standardized English/Slug is simpler.
                    // BUT converting existing Indonesian permissions to this new standard is a huge breaking change.
                    // Strategy: Start using "action menu-slug" (e.g. "view dashboard") for NEW/Dynamic logic.
                    // Check if we should translate "view" to "lihat" to match existing?
                    // User request: "simplify". 
                    // To be safe and compatible with the FrontendMenuSeeder we just read:
                    // It uses "lihat dashboard", "buat peran", etc.
                    
                    // LET'S TRY TO MATCH EXISTING INDONESIAN FORMAT FOR COMPATIBILITY
                    // Actions: view->lihat, create->buat, edit->ubah, delete->hapus, approve->aktivasi
                    
                    $idnAction = match($action) {
                        'view' => 'lihat',
                        'create' => 'buat',
                        'edit' => 'ubah',
                        'update' => 'ubah',
                        'delete' => 'hapus',
                        'approve' => 'aktivasi',
                        default => $action
                    };
                    
                    // We need the "object" name. In seeder it is "data santri" -> "santri"? 
                    // "Manajemen Staf" -> "staf"?
                    // This mapping is hard to guess dynamically.
                    // BETTER APPROACH for this "Simplify" task:
                    // Use the `en_title` or `id_title` and strict standard: "action menu_title".
                    // If the old permissions are different, we might migrate them or just add new ones.
                    // Let's create NEW permissions dynamically: "action menu_slug".
                    // e.g. "view student-banking", "create student-banking".
                    
                    $permName = "{$idnAction} {$menuSlug}";
                    
                    $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permName]);
                    $allPermissionIds[] = $permission->id;
                }
            }
            
            // Sync all collected permissions to the role
            // WARNING: This removes permissions not in the list. 
            // If the role has other permissions (not menu related), they might be lost?
            // "Concept-based" implies this IS the source of truth.
            $role->syncPermissions($allPermissionIds);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Akses role berhasil diperbarui',
                'data' => $role->load('menus', 'permissions')
            ], 200);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui akses role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync Permission Matrix for a role.
     * Uses scoped permissions: {action}_menu_{id} (e.g., view_menu_1)
     * 
     * @param Request $request
     * @param string $roleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncPermissionMatrix(Request $request, string $roleId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'matrix' => 'required|array',
                'matrix.*.menu_id' => 'required|exists:menus,id',
                'matrix.*.permissions' => 'array',
                'matrix.*.permissions.*' => 'in:CREATE,VIEW,EDIT,DELETE,APPROVE',
                'matrix.*.custom_permissions' => 'nullable|array',
                'matrix.*.custom_permissions.*' => 'string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::findOrFail($roleId);

            \Illuminate\Support\Facades\DB::beginTransaction();

            // Collect all menu IDs from matrix
            $menuIds = collect($request->matrix)->pluck('menu_id')->toArray();
            
            // Auto-sync parent menus: if a child menu is assigned, also assign its parent
            $allMenuIds = $menuIds;
            foreach ($menuIds as $menuId) {
                $menu = Menu::find($menuId);
                if ($menu && $menu->parent_id) {
                    $allMenuIds[] = $menu->parent_id;
                }
            }
            $allMenuIds = array_unique($allMenuIds);
            
            // 1. Sync Menus to Role (including auto-synced parents)
            $role->menus()->sync($allMenuIds);

            // 2. Sync Permissions
            $allPermissionNames = [];
            
            foreach ($request->matrix as $item) {
                $menuId = $item['menu_id'];
                $menu = Menu::find($menuId);
                $menuPermissionIds = [];
                
                // Standard permissions check
                // We use scoped permission names: {action}_menu_{id}
                $standardPermissions = $item['permissions'] ?? [];
                foreach ($standardPermissions as $action) {
                    $actionLower = strtolower($action);
                    $scopedName = "{$actionLower}_menu_{$menuId}";
                    
                    $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                        'name' => $scopedName,
                        'guard_name' => 'api'
                    ]);
                    
                    $allPermissionNames[] = $scopedName;
                    $menuPermissionIds[] = $permission->id;
                }
                
                // Custom permissions (if any)
                // For custom permissions, we also should scope them or keep them global?
                // Ideally scoped: {action}_menu_{id}
                $customPermissions = $item['custom_permissions'] ?? [];
                foreach ($customPermissions as $customPermName) {
                    // Assuming customPermName is the raw action name
                    $customActionLower = strtolower($customPermName);
                    $scopedName = "{$customActionLower}_menu_{$menuId}";
                    
                    $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                        'name' => $scopedName,
                        'guard_name' => 'api'
                    ]);
                    
                    $allPermissionNames[] = $scopedName;
                    $menuPermissionIds[] = $permission->id;
                }
                
                // Sync permissions to menu via menu_permissions pivot 
                // This marks "These permissions are ACTIVE for this menu"
                // It might need to be cumulative if multiple roles use diff permissions?
                // NO, because menu_permissions usually defines "Available permissions for this menu".
                // But here we are using it to track "Assigned permissions"?
                // Actually, Spatie's role_has_permissions tracks the assignment.
                // storing in menu_permissions is useful if we want to know "What permissions are relevant to Menu X".
                // We should probably ADD to menu_permissions, not sync (overwrite), 
                // but sync is cleaner to avoid orphans. 
                // LIMITATION: If we overwrite, we lose context of other roles?
                // No, menu_permissions keys menu_id <-> permission_id. 
                // It doesn't care about roles. 
                // So if Role A uses "view_menu_1" and Role B uses "view_menu_1", it's the same permission ID.
                // So syncing is fine as long as we include ALL relevant permissions?
                // Actually, if Role A adds View, and Role B adds Edit.
                // If we Sync for Role A (only View), do we remove Edit from Menu's list?
                // Yes if we use $menu->permissions()->sync().
                // FIX: use syncWithoutDetaching or just attach.
                // Or better: Don't manage menu_permissions here if it's supposed to be "Supported Permissions".
                // But since we are DYNAMICALLY creating permissions, we probably should link them.
                // Let's use syncWithoutDetaching to be safe for multiple roles.
                $menu->permissions()->syncWithoutDetaching($menuPermissionIds);
            }

            // 3. Sync all collected permissions to role
            // This replaces old permissions with new set for this role. Correct.
            $role->syncPermissions(array_unique($allPermissionNames));

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Permission matrix berhasil disinkronkan',
                'data' => $role->load('menus', 'permissions')
            ], 200);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyinkronkan permission matrix: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Permission Matrix for a role.
     * Returns hierarchical tree with all menus and their permission status.
     * 
     * @param string $roleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissionMatrix(string $roleId)
    {
        try {
            $role = Role::with('permissions')->findOrFail($roleId);
            $rolePermissions = $role->permissions->pluck('name')->toArray();
            
            // Get all sidebar menus
            $allMenus = Menu::where('position', 'sidebar')
                ->where('status', 'active')
                ->orderBy('order')
                ->get();
            
            // Build hierarchical tree with permission status
            $modules = $this->buildMenuMatrixTree($allMenus, null, $rolePermissions);
            
            // Calculate summary
            $leafMenus = $allMenus->filter(function($menu) {
                return $menu->route && $menu->route !== '#' && $menu->route !== '';
            });
            
            $assignedMenuIds = $role->menus()->pluck('menus.id')->toArray();
            $assignedLeafMenus = $leafMenus->whereIn('id', $assignedMenuIds)->count();
            
            $isFullAccess = $this->checkFullAccess($leafMenus, $rolePermissions);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'category' => $role->category,
                    ],
                    'summary' => [
                        'total_menus' => $leafMenus->count(),
                        'assigned_menus' => $assignedLeafMenus,
                        'is_full_access' => $isFullAccess,
                    ],
                    'modules' => $modules,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil permission matrix: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build hierarchical menu tree with permission status.
     */
    private function buildMenuMatrixTree($menus, $parentId, $rolePermissions)
    {
        $result = [];
        
        foreach ($menus->where('parent_id', $parentId) as $menu) {
            $node = [
                'id' => $menu->id,
                'title' => $menu->id_title,
                'route' => $menu->route,
                'is_group' => !$menu->route || $menu->route === '#' || $menu->route === '',
                'permissions' => $this->extractMenuPermissions($menu, $rolePermissions),
                'custom_permissions' => $this->extractMenuCustomPermissions($menu, $rolePermissions),
                'children' => $this->buildMenuMatrixTree($menus, $menu->id, $rolePermissions),
            ];
            
            $result[] = $node;
        }
        
        return $result;
    }

    /**
     * Extract standard permissions for a menu from role permissions.
     */
    private function extractMenuPermissions($menu, $rolePermissions)
    {
        $permissions = [];
        $standardActions = ['view', 'create', 'edit', 'delete', 'approve'];
        
        foreach ($standardActions as $action) {
            $scopedName = "{$action}_menu_{$menu->id}";
            if (in_array($scopedName, $rolePermissions)) {
                $permissions[] = strtoupper($action);
            }
        }
        
        // Legacy permission matching
        $menuSlug = \Illuminate\Support\Str::slug($menu->en_title ?? $menu->id_title);
        $actionMap = [
            'VIEW' => ['lihat', 'view', 'read'],
            'CREATE' => ['buat', 'create', 'add', 'tambah'],
            'EDIT' => ['ubah', 'edit', 'update'],
            'DELETE' => ['hapus', 'delete', 'remove'],
            'APPROVE' => ['setuju', 'approve', 'konfirmasi', 'aktivasi'],
        ];
        
        foreach ($rolePermissions as $rp) {
            $rpLower = strtolower($rp);
            foreach ($actionMap as $action => $verbs) {
                if (in_array($action, $permissions)) continue;
                foreach ($verbs as $verb) {
                    if (str_starts_with($rpLower, $verb) && str_ends_with($rpLower, $menuSlug)) {
                        $permissions[] = $action;
                        break 2;
                    }
                }
            }
        }
        
        return array_values(array_unique($permissions));
    }

    /**
     * Extract custom permissions for a menu from role permissions.
     */
    private function extractMenuCustomPermissions($menu, $rolePermissions)
    {
        $customPermissions = [];
        $standardActions = ['view', 'create', 'edit', 'delete', 'approve'];
        $standardVerbs = ['lihat', 'view', 'read', 'buat', 'create', 'add', 'tambah', 'ubah', 'edit', 'update', 'hapus', 'delete', 'remove', 'setuju', 'approve', 'konfirmasi', 'aktivasi'];
        
        foreach ($rolePermissions as $rp) {
            $rpLower = strtolower($rp);
            
            // Scoped custom permissions
            if (str_ends_with($rpLower, "_menu_{$menu->id}")) {
                $suffix = "_menu_{$menu->id}";
                $action = substr($rpLower, 0, strlen($rpLower) - strlen($suffix));
                if (!in_array($action, $standardActions)) {
                    $customPermissions[] = $action;
                }
                continue;
            }
            
            // Legacy custom permissions
            $menuSlug = \Illuminate\Support\Str::slug($menu->en_title ?? $menu->id_title);
            if (str_ends_with($rpLower, " {$menuSlug}")) {
                $words = explode(' ', $rpLower);
                $firstWord = $words[0] ?? '';
                if (!in_array($firstWord, $standardVerbs)) {
                    $customPermissions[] = $rp;
                }
            }
        }
        
        return array_values(array_unique($customPermissions));
    }

    /**
     * Check if role has full access to all leaf menus.
     */
    private function checkFullAccess($leafMenus, $rolePermissions)
    {
        foreach ($leafMenus as $menu) {
            foreach (['view', 'create', 'edit', 'delete', 'approve'] as $action) {
                $scopedName = "{$action}_menu_{$menu->id}";
                if (!in_array($scopedName, $rolePermissions)) {
                    return false;
                }
            }
        }
        return true;
    }
}
