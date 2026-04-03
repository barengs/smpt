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

        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File not found: ' . $path], 404);
        }

        $type = Storage::disk('public')->mimeType($path);
        $file = Storage::disk('public')->get($path);

        // If the file is webp, convert it to png for PDF compatibility
        if ($type === 'image/webp') {
            try {
                $file = Image::read($file)->toPng()->toBuffer();
                $type = 'image/png';
                $path = str_replace('.webp', '.png', $path);
            } catch (\Exception $e) {
                // If conversion fails, fall back to original file
            }
        }

        return Response::make($file, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'public, max-age=86400', // Cache for 1 day
        ]);
    }
}
