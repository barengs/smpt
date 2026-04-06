<?php
// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Read last 10KB of laravel.log
$logFile = storage_path('logs/laravel.log');
$size = filesize($logFile);
$offset = max(0, $size - 10000);

$fh = fopen($logFile, 'r');
fseek($fh, $offset);
$content = fread($fh, 10000);
fclose($fh);

// Find start from newline boundary
$pos = strpos($content, "\n");
if ($pos !== false) $content = substr($content, $pos + 1);

// Only show lines from last hour
$lines = explode("\n", $content);
$recent = [];
foreach ($lines as $line) {
    if (preg_match('/\[2026-04-06 1[2-9]:/', $line) || !empty($recent)) {
        $recent[] = $line;
    }
}
// Show last 100 lines
$recent = array_slice($recent, -120);
echo implode("\n", $recent);
