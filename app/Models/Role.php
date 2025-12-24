<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\Menu;

class Role extends SpatieRole
{
    public function menus()
    {
        return $this->belongsToMany(
            Menu::class,
            'role_menu',
            'role_id',
            'menu_id'
        );
    }
}
