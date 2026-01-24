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
    // create dummy csv
    $csvContent = "first_name,last_name,nik,kk,gender,parent_as,card_address,domicile_address,phone,email,occupation_id,education_id\n";
    $csvContent .= "TestFather,TestLast,'1234567890123456,'9876543210987654,L,ayah,Jalan Test,Jalan Domisili,'08123456789,test@mail.com,1,1\n";
    
    $filePath = sys_get_temp_dir() . '/test_import.csv';
    file_put_contents($filePath, $csvContent);
    
    $file = new UploadedFile($filePath, 'test_import.csv', 'text/csv', null, true);
    
    echo "File created at: $filePath\n";
    
    $import = new ParentsImport();
    Excel::import($import, $file);
    
    echo "Import Successful!\n";
    print_r($import->getErrors());
    
} catch (\Throwable $e) {
    echo "CAUGHT ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
