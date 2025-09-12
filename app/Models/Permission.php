<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
class Permission extends SpatiePermission
{
    public function menu()
    {
        return $this->belongsToMany(Menu::class, 'menu_permissions');
    }
}
