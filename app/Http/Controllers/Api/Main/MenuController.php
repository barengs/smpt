<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class MenuController extends Controller
{
    /**
     * Get menus for the authenticated user based on their role.
     * Returns a hierarchical menu structure.
     */
    public function getUserMenus()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get all roles for the user
            $roles = $user->roles;
            
            if ($roles->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No menus available',
                    'data' => []
                ], 200);
            }

            // Get all menu IDs assigned to user's roles
            $menuIds = [];
            foreach ($roles as $role) {
                $roleMenuIds = $role->menus()->pluck('menu_id')->toArray();
                $menuIds = array_merge($menuIds, $roleMenuIds);
            }
            
            // Remove duplicates
            $menuIds = array_unique($menuIds);

            if (empty($menuIds)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No menus assigned to user roles',
                    'data' => []
                ], 200);
            }

            // Fetch menus with their children
            $menus = Menu::whereIn('id', $menuIds)
                ->where('status', 'active')
                ->where('position', 'sidebar')
                ->whereNull('parent_id')
                ->orderBy('order')
                ->get();

            // Build hierarchical structure
            $menuTree = $menus->map(function ($menu) use ($menuIds) {
                return $this->buildMenuNode($menu, $menuIds);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'User menus retrieved successfully',
                'data' => $menuTree
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve user menus: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build a menu node with its children
     */
    private function buildMenuNode($menu, $menuIds)
    {
        $node = [
            'id' => $menu->id,
            'id_title' => $menu->id_title,
            'en_title' => $menu->en_title,
            'ar_title' => $menu->ar_title,
            'description' => $menu->description,
            'icon' => $menu->icon,
            'route' => $menu->route,
            'parent_id' => $menu->parent_id,
            'type' => $menu->type,
            'position' => $menu->position,
            'status' => $menu->status,
            'order' => $menu->order,
        ];

        // Get children that are in the user's assigned menus
        $children = Menu::where('parent_id', $menu->id)
            ->whereIn('id', $menuIds)
            ->where('status', 'active')
            ->where('position', 'sidebar')
            ->orderBy('order')
            ->get();

        if ($children->isNotEmpty()) {
            $node['children'] = $children->map(function ($child) use ($menuIds) {
                return $this->buildMenuNode($child, $menuIds);
            })->toArray();
        } else {
            $node['children'] = [];
        }

        return $node;
    }

    /**
     * Get all menus (admin only)
     */
    public function index()
    {
        try {
            $menus = Menu::whereNull('parent_id')
                ->with('child')
                ->orderBy('order')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Menus retrieved successfully',
                'data' => $menus
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve menus: ' . $e->getMessage()
            ], 500);
        }
    }
}
