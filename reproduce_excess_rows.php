<?php

use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParentsImport;
use App\Exports\ParentTemplateExport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Starting Excess Rows Simulation...\n";

try {
    // 1. Generate Template
    $templateName = 'test_template.xlsx';
    $templatePath = sys_get_temp_dir() . '/' . $templateName;
    
    echo "Generating template...\n";
    // We can't easily generate an actual Excel file with Maatwebsite/Excel in this script without full app context 
    // and writer dependencies, but we can try to use the Export class if it works.
    // Alternatively, we create a CSV with many empty lines.
    
    $csvContent = "nik,kk,first_name,last_name,gender,parent_as,card_address,domicile_address,phone,email,occupation_id,education_id\n";
    $csvContent .= "1234567890123451,1234567890123451,TestOne,Last,L,ayah,Addr,Addr,081,test1@mail.com,1,1\n";
    $csvContent .= "1234567890123452,1234567890123452,TestTwo,Last,P,ibu,Addr,Addr,082,test2@mail.com,1,1\n";
    
    // Add 100 empty lines (simulating Excel "used range" issue)
    for ($i = 0; $i < 100; $i++) {
        $csvContent .= ",,,,,,,,,,,\n"; 
    }
    
    file_put_contents($templatePath, $csvContent);
    $finalPath = str_replace('.xlsx', '.csv', $templatePath); 
    // Actually templatePath was constructed with xlsx extension but we want csv file content.
    // Let's simplified path handling
    $finalPath = sys_get_temp_dir() . '/test_template_excess.csv';
    file_put_contents($finalPath, $csvContent);
    
    $file = new UploadedFile($finalPath, 'test_template.csv', 'text/csv', null, true);
    
    echo "File created at: $finalPath with 2 valid rows and 100 empty rows\n";
    
    $import = new ParentsImport();
    Excel::import($import, $file);
    
    echo "Import Completed.\n";
    echo "Success Count: " . $import->getSuccessCount() . "\n";
    echo "Failure Count: " . $import->getFailureCount() . "\n";
    echo "Errors:\n";
    print_r(array_slice($import->getErrors(), 0, 5));

} catch (\Throwable $e) {
    echo "CAUGHT ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
