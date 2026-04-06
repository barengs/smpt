<?php
// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test 1: Cek env vars
$key = env('BANK_SANTRI_INTERNAL_KEY', 'NOT_FOUND');
$bankUrl = env('BANK_SANTRI_URL', 'NOT_FOUND');

echo "BANK_SANTRI_INTERNAL_KEY: $key\n";
echo "BANK_SANTRI_URL: $bankUrl\n\n";

// Test 2: Test request via Laravel Http
try {
    $res = \Illuminate\Support\Facades\Http::withHeaders([
        'X-Internal-Key' => $key,
        'Accept'         => 'application/json',
        'Content-Type'   => 'application/json',
    ])->post("{$bankUrl}/api/internal/account", [
        'account_number' => 'DEBUG_TEST_999',
        'customer_id'    => 1,
        'customer_name'  => 'Debug Test',
        'product_id'     => 1,
    ]);

    echo "HTTP Status: " . $res->status() . "\n";
    echo "Response: " . $res->body() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
