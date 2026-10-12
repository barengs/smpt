<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\UserDeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @tags FCM Notifications
 *
 * APIs for registering and unregistering user device tokens for push notifications.
 */
class FcmTokenController extends Controller
{
    /**
     * Daftarkan atau perbarui device token pengguna yang terautentikasi
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'device_type' => 'nullable|string|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], 401);
        }

        $deviceToken = UserDeviceToken::updateOrCreate(
            [
                'user_id' => $userId,
                'token' => $request->token,
            ],
            [
                'device_type' => $request->device_type,
                'device_name' => $request->device_name,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Device token berhasil didaftarkan',
            'data' => $deviceToken,
        ], 200);
    }

    /**
     * Hapus device token saat logout atau notifikasi dimatikan
     */
    public function unregister(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token dibutuhkan',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = Auth::id();
        if ($userId) {
            UserDeviceToken::where('user_id', $userId)
                ->where('token', $request->token)
                ->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Device token berhasil dihapus',
        ], 200);
    }
}
