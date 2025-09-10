<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Exception;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $news = News::all();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil diambil',
                'data' => $news
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data berita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|string',
                'is_published' => 'nullable|in:draft,publish'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $news = News::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Berita berhasil dibuat',
                'data' => $news
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat berita: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $news = News::find($id);

            if (!$news) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Berita tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data berita berhasil diambil',
                'data' => $news
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data berita: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $news = News::find($id);

            if (!$news) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Berita tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'image' => 'nullable|string',
                'is_published' => 'nullable|in:draft,publish'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $news->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Berita berhasil diperbarui',
                'data' => $news
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui berita: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $news = News::find($id);

            if (!$news) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Berita tidak ditemukan'
                ], 404);
            }

            $news->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Berita berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus berita: ' . $e->getMessage()
            ], 500);
        }
    }
}
