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
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
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

        // Check if the login input is an email or name
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        // Attempt to authenticate with email or name
        if (! $token = auth()->attempt([$field => $login, 'password' => $password])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token, auth()->user());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->user();
        $role = $user->getRoleNames(); // Get user roles
        if ($role[0] == 'orangtua') {
            $user->profile = $user->parent; // Attach parent profile if user is a parent
        } else {
            $user->profile = $user->staff; // Attach employee profile if user is a teacher
        }
        // $user->profile = $user->profile(); // Attach profile based on role
        return response()->json(['data' => $user]);
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
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user
        ]);
    }
}
