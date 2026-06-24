<?php

$modelsPath = __DIR__.'/app/Models';
$models = [];
foreach (glob($modelsPath.'/*.php') as $file) {
    $models[basename($file, '.php')] = file_get_contents($file);
}

$relations = [];
foreach ($models as $modelName => $content) {
    // Match methods that return $this->belongsTo(..., $this->hasMany(..., etc
    if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(\)\s*\{[^\}]*?return\s+\$this->(belongsTo|hasMany|hasOne|belongsToMany)\s*\(\s*([a-zA-Z0-9_]+)::class/s', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $method = $match[1];
            $type = $match[2];
            $target = $match[3];
            
            if ($type == 'belongsTo') {
                $relations[] = "$modelName }o--|| $target : \"$method\"";
            } elseif ($type == 'hasMany') {
                $relations[] = "$modelName ||--o{ $target : \"$method\"";
            } elseif ($type == 'hasOne') {
                $relations[] = "$modelName ||--o| $target : \"$method\"";
            } elseif ($type == 'belongsToMany') {
                $relations[] = "$modelName }o--o{ $target : \"$method\"";
            }
        }
    }
}

file_put_contents('relations.txt', implode("\n", $relations));
echo "Done\n";
