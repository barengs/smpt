<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\StudentCardSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use Intervention\Image\Laravel\Facades\Image;

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
            'front_template' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'back_template' => 'required|image|mimes:jpeg,png,jpg|max:2048',
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

        // Use firstOrNew with checks, but let's assume we want to work with the active one.
        // If multiple active exist (shouldn't happen per design), take first. 
        // If none, create new instance.
        $setting = StudentCardSetting::where('is_active', true)->first();
        if (!$setting) {
            $setting = new StudentCardSetting();
            $setting->is_active = true;
        }

        // Handle File Uploads
        $fields = ['front_template', 'back_template', 'stamp', 'signature'];
        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);

                // Delete old file if exists
                if ($setting->$field && Storage::disk('public')->exists($setting->$field)) {
                    Storage::disk('public')->delete($setting->$field);
                }

                // Generate Filename (hash + .webp)
                $filename = $file->hashName();
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
                $path = 'student-card/' . $filename;

                // Create Image using Intervention and encode to WebP
                // Using v3 style: Image::read($file)->toWebp() or similar.
                // Facade in v3 might map to ImageManager.
                // Let's use standard Image::read() if using simple facade, 
                // or check the typical facade usage in Laravel integration.
                // Assuming typical 'Intervention\Image\Laravel\Facades\Image' works as 'Image::read'
                
                $image = Image::read($file);
                
                // Encode to WebP
                $encoded = $image->toWebp(80); // quality 80

                // Save to storage using Laravel Storage
                Storage::disk('public')->put($path, (string) $encoded);

                // Update model
                $setting->$field = $path;
            }
        }

        $setting->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Student card configuration updated successfully',
            'data' => $setting
        ]);
    }
}
