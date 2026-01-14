<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Get menus associated with this role.
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

    /**
     * Scope a query to only include roles of a given category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
