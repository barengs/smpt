<?php

use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParentsImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Starting Import Simulation...\n";

// Debug Roles
echo "Existing Roles:\n";
$roles = \Spatie\Permission\Models\Role::all();
foreach ($roles as $role) {
    echo "- {$role->name} (guard: {$role->guard_name})\n";
}
echo "----------------\n";

try {
    // Use the actual file provided by the user
    // Use the actual file provided by the user
    $realFilePath = '/Users/rofi/Development/pesantren/smpt/wali_santri_backup_2026-01-24.csv';
    
    if (!file_exists($realFilePath)) {
        throw new Exception("File not found: $realFilePath");
    }

    $file = new UploadedFile($realFilePath, 'wali_santri_backup.csv', 'text/csv', null, true);
    
    echo "Using real file at: $realFilePath\n";
    
    $import = new ParentsImport();
    Excel::import($import, $file);
    
    echo "Import Successful!\n";
    print_r($import->getErrors());
    
} catch (\Throwable $e) {
    echo "CAUGHT ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
