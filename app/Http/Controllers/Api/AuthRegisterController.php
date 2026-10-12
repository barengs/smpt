<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ParentProfile;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthRegisterController extends Controller
{
    /**
     * Register a new user (Wali Santri / umum)
     *
     * Mendukung pengisian NIK & Nomor HP opsional saat registrasi.
     * Jika NIK diisi, sistem otomatis membuatkan profil wali (parent_profiles)
     * sehingga wali dapat langsung login dengan NIK / No HP di kemudian hari.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'nik' => 'nullable|string|digits:16|unique:parent_profiles,nik',
            'phone' => 'nullable|string|max:20',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign role orangtua jika tersedia di sistem RBAC
            if (\Spatie\Permission\Models\Role::where('name', 'orangtua')->exists()) {
                $user->assignRole('orangtua');
            }

            // Jika NIK diisi, buatkan profil wali (parent_profiles)
            if ($request->filled('nik')) {
                ParentProfile::create([
                    'user_id' => $user->id,
                    'nik' => $request->nik,
                    'kk' => $request->nik, // fallback: KK default diisi sementara dengan NIK
                    'first_name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ]);
            }

            DB::commit();

            // Load relasi parent & roles untuk response
            $user->load('parent');
            $user->load('roles');

            // Generate JWT token for the newly registered user
            $token = JWTAuth::fromUser($user);

            // Return success response with token
            return $this->respondWithToken($token, $user);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Pendaftaran gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     * @param  \App\Models\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $user
        ]);
    }
}
