<?php

namespace App\Services\Notification;

use App\Models\Student;
use App\Models\UserDeviceToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class FirebaseNotificationService
{
    /**
     * Kirim push notification ke satu token spesifik
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $messaging = Firebase::messaging();
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withToken($token)
                ->withNotification($notification)
                ->withData($data);

            $messaging->send($message);
            return true;
        } catch (Throwable $e) {
            Log::error('FCM Send Error: ' . $e->getMessage(), [
                'token' => $token,
                'title' => $title,
            ]);
            return false;
        }
    }

    /**
     * Kirim push notification ke semua device milik user tertentu (misal: wali santri)
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): int
    {
        $tokens = UserDeviceToken::where('user_id', $userId)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return 0;
        }

        $successCount = 0;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Kirim push notification ke banyak user (batch)
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): int
    {
        $tokens = UserDeviceToken::whereIn('user_id', $userIds)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return 0;
        }

        $successCount = 0;
        foreach ($tokens as $token) {
            if ($this->sendToToken($token, $title, $body, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Kirim push notification ke wali santri berdasarkan student_id
     */
    public function sendToStudentParent(int $studentId, string $title, string $body, array $data = []): int
    {
        $student = Student::with('parents')->find($studentId);
        $userId = $student?->parents?->user_id;

        if (!$userId) {
            return 0;
        }

        return $this->sendToUser((int) $userId, $title, $body, $data);
    }
}
