<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\LessonHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class LessonHourController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $lessonHours = LessonHour::orderByDesc('id')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data jam pelajaran berhasil diambil',
                'data' => $lessonHours
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jam pelajaran',
                'error' => $e->getMessage()
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
                'name' => 'required|string|max:255',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'order' => 'nullable|integer',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $lessonHour = LessonHour::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Jam pelajaran berhasil ditambahkan',
                'data' => $lessonHour
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan jam pelajaran',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan jam pelajaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $lessonHour = LessonHour::find($id);

            if (!$lessonHour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam pelajaran tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data jam pelajaran berhasil diambil',
                'data' => $lessonHour
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jam pelajaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $lessonHour = LessonHour::find($id);

            if (!$lessonHour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam pelajaran tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'order' => 'nullable|integer',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $lessonHour->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Jam pelajaran berhasil diperbarui',
                'data' => $lessonHour
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jam pelajaran',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui jam pelajaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $lessonHour = LessonHour::find($id);

            if (!$lessonHour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam pelajaran tidak ditemukan'
                ], 404);
            }

            $lessonHour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jam pelajaran berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jam pelajaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $lessonHour = LessonHour::withTrashed()->find($id);

            if (!$lessonHour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam pelajaran tidak ditemukan'
                ], 404);
            }

            if (!$lessonHour->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam pelajaran tidak dalam keadaan terhapus'
                ], 400);
            }

            $lessonHour->restore();

            return response()->json([
                'success' => true,
                'message' => 'Jam pelajaran berhasil dipulihkan',
                'data' => $lessonHour
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan jam pelajaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the trashed resource.
     */
    public function trashed()
    {
        try {
            $lessonHours = LessonHour::onlyTrashed()->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data jam pelajaran terhapus berhasil diambil',
                'data' => $lessonHours
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jam pelajaran terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
