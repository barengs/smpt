<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class ImageProxyController extends Controller
{
    /**
     * Proxy a storage image to bypass CORS restrictions.
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

        $file = Storage::disk('public')->get($path);
        $type = Storage::disk('public')->mimeType($path);

        return Response::make($file, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'public, max-age=86400', // Cache for 1 day
        ]);
    }
}
