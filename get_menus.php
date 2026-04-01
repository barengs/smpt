<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$menus = \App\Models\Menu::where('id_title', 'Bank Santri')
    ->orWhere('parent_id', 2)
    ->get(['id', 'id_title', 'route', 'parent_id'])
    ->toArray();

echo json_encode($menus, JSON_PRETTY_PRINT);
