<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Menu;
use App\Models\User;

$user = User::where('email', 'admin@gmail.com')->first();
if (!$user) {
    echo "No user found.\n";
    $user = User::first();
}

echo "User: " . $user->email . "\n";

$roles = $user->roles;
$menuIds = [];
foreach ($roles as $role) {
    $roleMenuIds = $role->menus()->pluck('menu_id')->toArray();
    $menuIds = array_merge($menuIds, $roleMenuIds);
}
$menuIds = array_unique($menuIds);
$userMenuIds = $menuIds; // Keep a copy for recursion

// Filter for Roots
$menus = Menu::whereIn('id', $menuIds)
    ->where('status', 'active')
    ->where('position', 'sidebar')
    ->whereNull('parent_id')
    ->orderBy('order')
    ->get();

function buildMenuNodeLocal($menu, $menuIds)
{
    $node = [
        'id' => $menu->id,
        'title' => $menu->id_title,
        'children' => []
    ];

    $children = Menu::where('parent_id', $menu->id)
        ->whereIn('id', $menuIds)
        ->where('status', 'active')
        ->where('position', 'sidebar')
        ->orderBy('order')
        ->get();

    if ($children->isNotEmpty()) {
        $node['children'] = $children->map(function ($child) use ($menuIds) {
            return buildMenuNodeLocal($child, $menuIds);
        })->toArray();
    }

    return $node;
}

$menuTree = $menus->map(function ($menu) use ($userMenuIds) {
    return buildMenuNodeLocal($menu, $userMenuIds);
})->toArray(); // Ensure array conversion

echo json_encode($menuTree, JSON_PRETTY_PRINT);
