<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Critical info only
$cols = Schema::getColumnListing('transaction_types');
$tt1 = DB::table('transaction_types')->where('id', 1)->first();

echo "COLS: " . implode('|', $cols) . "\n";
echo "TT1 debit_coa: " . ($tt1->default_debit_coa ?? 'NULL') . "\n";
echo "TT1 credit_coa: " . ($tt1->default_credit_coa ?? 'NULL') . "\n";
echo "TT1 name: " . ($tt1->name ?? 'NULL') . "\n";

$ledgerCols = Schema::getColumnListing('transaction_ledgers');
echo "LEDGER COLS: " . implode('|', $ledgerCols) . "\n";

$transCols = Schema::getColumnListing('transactions');
echo "TRANS COLS: " . implode('|', $transCols) . "\n";

$prod1 = DB::table('products')->where('id', 1)->first();
$exists = DB::table('products')->exists();
echo "PRODUCT EXISTS: " . ($exists ? 'yes' : 'no') . "\n";
if ($prod1) {
    echo "PRODUCT1: id=" . $prod1->id . " name=" . $prod1->name . " opening_fee=" . $prod1->opening_fee . "\n";
}
