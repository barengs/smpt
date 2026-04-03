<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageProxyController extends Controller
{
    /**
     * Proxy a storage image to bypass CORS restrictions and convert formats.
     * Path should be relative to 'public' disk (e.g., student-card/abc.webp)
     */
    public function proxy(Request $request)
    {
        $path = $request->query('path');

        if (!$path) {
            return response()->json(['message' => 'Path is required'], 400);
        }

        // Security: Prevent directory traversal
        if (Str::contains($path, '../') || Str::contains($path, '..\\')) {
            return response()->json(['message' => 'Invalid path'], 400);
        }

        // Try to get the file using the public disk
        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File not found: ' . $path], 404);
        }

        $file = Storage::disk('public')->get($path);
        
        // Use native PHP to get mime type securely without depending on 'fileinfo' extension
        $info = @getimagesizefromstring($file);
        $type = $info['mime'] ?? 'image/jpeg';

        // Conversion using native GD (failsafe approach)
        // This avoids dependency on the Intervention library which might be misconfigured
        if ($type === 'image/webp' && function_exists('imagecreatefromwebp')) {
            try {
                // Read webp and convert to png
                $im = @imagecreatefromwebp('data://image/webp;base64,' . base64_encode($file));
                if ($im) {
                    ob_start();
                    imagepng($im);
                    $processedFile = ob_get_clean();
                    if ($processedFile) {
                        $file = $processedFile;
                        $type = 'image/png';
                    }
                    imagedestroy($im);
                }
            } catch (\Throwable $e) {
                // If native conversion fails, we fall back to sending original file
            }
        }

        return Response::make($file, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'public, max-age=86400', // Cache for 1 day
        ]);
    }
}
