<?php
// Bootstrap Laravel
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Imports\RegistrationsImport;
use Maatwebsite\Excel\Facades\Excel;

try {
    $import = new RegistrationsImport();
    Excel::import($import, 'calon_santri.xlsx');
    
    echo "SUCCESS COUNT: " . $import->getSuccessCount() . "\n";
    echo "FAILURE COUNT: " . $import->getFailureCount() . "\n";
    echo "SKIPPED COUNT: " . $import->getSkippedCount() . "\n";
    echo "WARNINGS: \n";
    print_r($import->getWarnings());
    echo "ERRORS: \n";
    print_r($import->getErrors());
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
