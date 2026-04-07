<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$village = \App\Models\Region\Village::take(1)->first();
echo "Village: id=" . $village->id . ", code=" . $village->code . "\n";

$reg = \App\Models\Registration::whereNotNull('village_id')->take(1)->first(['id', 'village_id']);
echo "Registration village_id: " . $reg->village_id . "\n";
