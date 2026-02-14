<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nik = '3528012345670001';
$password = 'password';

echo "Checking user with NIK (email): $nik\n";

$user = User::where('email', $nik)->first();

if ($user) {
    echo "User found!\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
    
    if (Hash::check($password, $user->password)) {
        echo "Password matches!\n";
    } else {
        echo "Password DOES NOT match.\n";
        echo "Hashed Password in DB: " . $user->password . "\n";
        echo "New Hash of '$password': " . Hash::make($password) . "\n";
    }
} else {
    echo "User NOT found with email = $nik\n";
    
    echo "User not found with email = $nik\n";
    echo "Creating user now...\n";
    
    $newUser = User::create([
        'name' => 'Wali Santri Test',
        'email' => $nik,
        'password' => Hash::make($password),
    ]);
    
    $newUser->assignRole('orangtua');
    
    // Create dummy profile
    \App\Models\ParentProfile::create([
        'user_id' => $newUser->id,
        'father_name' => 'Father Name',
        'mother_name' => 'Mother Name',
        // Add other required fields if any, based on inspection
    ]);

    echo "User created successfully with ID: " . $newUser->id . "\n";
}
