<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Get the menus associated with this role.
     */
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
