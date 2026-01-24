<?php

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

class DebugImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        echo "Headers detected (keys of first row):\n";
        if ($rows->count() > 0) {
            print_r(array_keys($rows[0]->toArray()));
            
            echo "\nFirst Row Data:\n";
            print_r($rows[0]->toArray());
        } else {
            echo "No data found.\n";
        }
    }
}

try {
    $file = '/Users/rofi/Development/pesantren/smpt/wali_santri_backup_2026-01-24.xlsx';
    echo "Reading: $file\n";
    
    Excel::import(new DebugImport, $file);
    
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
