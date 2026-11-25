<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageProxyController extends Controller
{
    /**
     * Proxy a storage image to bypass CORS restrictions and convert formats.
     * Path should be relative to 'public' disk (e.g., student-card/abc.webp)
     */
    public function proxy(Request $request)
    {
        $path = (string) $request->query('path', '');

        if (trim($path) === '') {
            return response()->json(['message' => 'Path is required'], 400);
        }

        // Security: Prevent directory traversal and null-byte injection
        if (Str::contains($path, ['../', '..\\', "\0"])) {
            return response()->json(['message' => 'Invalid path'], 400);
        }

        $cleanPath = preg_replace('#^storage/#i', '', ltrim(str_replace('\\', '/', trim($path)), '/'));

        if (! $cleanPath || ! Storage::disk('public')->exists($cleanPath)) {
            return response()->json(['message' => 'File not found: ' . $cleanPath], 404);
        }

        $file = Storage::disk('public')->get($cleanPath);

        // Use native PHP to get mime type securely without depending on 'fileinfo' extension
        $info = @getimagesizefromstring($file);
        $type = $info['mime'] ?? 'image/png';

        // Convert WebP (unsupposed in @react-pdf/renderer) to PNG using native GD or Intervention Image
        if ($type === 'image/webp') {
            $converted = false;

            if (function_exists('imagecreatefromwebp')) {
                try {
                    $im = @imagecreatefromwebp('data://image/webp;base64,' . base64_encode($file));
                    if ($im) {
                        imagealphablending($im, false);
                        imagesavealpha($im, true);
                        ob_start();
                        imagepng($im);
                        $processedFile = ob_get_clean();
                        imagedestroy($im);
                        if ($processedFile) {
                            $file = $processedFile;
                            $type = 'image/png';
                            $converted = true;
                        }
                    }
                } catch (\Throwable $e) {
                    // Fallback to Intervention Image below
                }
            }

            if (! $converted) {
                try {
                    $manager = new ImageManager(new Driver());
                    $encoded = $manager->read($file)->toPng();
                    $file = (string) $encoded;
                    $type = 'image/png';
                } catch (\Throwable $e) {
                    // If conversion fails, send original file
                }
            }
        }

        return Response::make($file, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline; filename="' . basename($cleanPath) . '"',
            'Cache-Control' => 'public, max-age=86400',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        ]);
    }
}
