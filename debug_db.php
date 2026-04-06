<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Check TransactionType table structure
echo "=== TransactionType columns ===\n";
$cols = Schema::getColumnListing('transaction_types');
echo implode(', ', $cols) . "\n\n";

// Check data
echo "=== TransactionType data ===\n";
$types = DB::table('transaction_types')->get();
echo json_encode($types, JSON_PRETTY_PRINT) . "\n\n";

// Check Transaction table
echo "=== Transaction columns ===\n";
$cols2 = Schema::getColumnListing('transactions');
echo implode(', ', $cols2) . "\n\n";

// Check TransactionLedger table
echo "=== TransactionLedger columns ===\n";
$cols3 = Schema::getColumnListing('transaction_ledgers');
echo implode(', ', $cols3) . "\n\n";

// Check Product table
echo "=== Product data ===\n";
$products = DB::table('products')->get();
echo json_encode($products, JSON_PRETTY_PRINT) . "\n";
