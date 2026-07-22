<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$validator = \Illuminate\Support\Facades\Validator::make(
    ['position_id' => 1, 'staff_id' => 1, 'start_date' => '2026-07-22', 'is_active' => true],
    [
        'position_id' => 'required|exists:positions,id',
        'staff_id' => 'required|exists:staff,id',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after:start_date',
        'assignment_letter' => 'nullable|string',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ]
);
echo json_encode($validator->errors()->toArray());
