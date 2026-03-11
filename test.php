<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/api/master/class-group/details', 'GET', ['academic_year_id' => 2]);
$controller = $app->make(\App\Http\Controllers\Api\Master\ClassGroupController::class);
$response = $controller->getClassGroupsWithDetails($request);

echo $response->getContent();
