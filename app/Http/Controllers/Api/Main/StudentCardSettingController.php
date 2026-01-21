<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\StudentCardSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentCardSettingController extends Controller
{
    /**
     * Get the current student card configuration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $setting = StudentCardSetting::where('is_active', '=', true)->first();

        if (!$setting) {
            // Return empty structure or create default
            return response()->json([
                'status' => 'success',
                'data' => null
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $setting
        ]);
    }

    /**
     * Update or Create student card configuration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'front_template' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'back_template' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'stamp' => 'nullable|image|mimes:jpeg,png,jpg,png|max:1024',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,png|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $setting = StudentCardSetting::firstOrNew(['is_active' => true]);

        // Handle File Uploads
        $fields = ['front_template', 'back_template', 'stamp', 'signature'];
        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                // Delete old file if exists
                if ($setting->$field && Storage::disk('public')->exists($setting->$field)) {
                    Storage::disk('public')->delete($setting->$field);
                }

                // Upload new file
                $path = $request->file($field)->store('student-card', 'public');
                $setting->$field = $path;
            }
        }

        $setting->is_active = true;
        $setting->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Student card configuration updated successfully',
            'data' => $setting
        ]);
    }
}
