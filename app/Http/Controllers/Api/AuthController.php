<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh']]);
    }

    /**
     * Login user and create token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Get the login input (either email or name)
        $login = $request->input('login');
        $password = $request->input('password');

        $user = null;
        if (is_numeric($login)) {
            // 1. Try to find user by email (in case email column stores NIK)
            $user = User::where('email', $login)->first();
            
            // 2. Try to find user by parent NIK
            if (!$user) {
                $user = User::whereHas('parent', function ($query) use ($login) {
                    $query->where('nik', $login);
                })->first();
            }

            // 3. Try to find user by staff NIK
            if (!$user) {
                $user = User::whereHas('staff', function ($query) use ($login) {
                    $query->where('nik', $login);
                })->first();
            }
        }

        // If a user is resolved, attempt login with their email
        if ($user) {
            $token = auth()->attempt(['email' => $user->email, 'password' => $password]);
        } else {
            // Fallback to original logic if no user found
            $field = (filter_var($login, FILTER_VALIDATE_EMAIL) || is_numeric($login)) ? 'email' : 'name';
            $token = auth()->attempt([$field => $login, 'password' => $password]);
        }

        if (! $token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        if ($user->hasRole('orangtua'))
        {
            $user->profile = $user->parent;
        } else {
            $user->profile = $user->staff;
        }

        return $this->respondWithToken($token, $user);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        if ($user->hasRole('orangtua')) {
            $user->profile = $user->parent; // Attach parent profile if user is a parent
        } else {
            $user->profile = $user->staff; // Attach employee profile if user is a teacher
        }
        
        // Transform user to array and add permissions
        $userData = $user->toArray();
        $userData['permissions'] = $user->getAllPermissions()->pluck('name');
        
        return response()->json(['data' => $userData]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh(), auth()->user());
    }

    /**
     * SSO Handover to Bank Santri.
     * Generates a token and redirects to Bank Santri.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function ssoHandover()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Get the primary role - optimized retrieval
        $role = $user->getRoleNames()->first() ?? 'staff';

        // Generate token with custom claims
        $token = auth()->claims([
            'role' => $role,
            'name' => $user->name,
            'email' => $user->email,
        ])->fromUser($user);

        $bankUrl = config('app.bank_santri_url', env('BANK_SANTRI_URL', 'http://localhost:8001'));
        
        return redirect()->away($bankUrl . '/auth/sso?token=' . $token);
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
        // Transform user to array and add permissions
        $userData = $user->toArray();
        $userData['permissions'] = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $userData
        ]);
    }
}
