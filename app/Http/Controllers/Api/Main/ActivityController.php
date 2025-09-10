<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Exception;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $activities = Activity::all();

            return response()->json([
                'status' => 'success',
                'message' => 'Data aktivitas berhasil diambil',
                'data' => $activities
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data aktivitas: ' . $e->getMessage()
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
                'description' => 'nullable|string',
                'date' => 'nullable|date',
                'status' => 'nullable|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $activity = Activity::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Aktivitas berhasil dibuat',
                'data' => $activity
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat aktivitas: ' . $e->getMessage()
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
            $activity = Activity::find($id);

            if (!$activity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aktivitas tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data aktivitas berhasil diambil',
                'data' => $activity
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data aktivitas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $activity = Activity::find($id);

            if (!$activity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aktivitas tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'nullable|date',
                'status' => 'nullable|in:active,inactive'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $activity->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Aktivitas berhasil diperbarui',
                'data' => $activity
            ], 200);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui aktivitas: ' . $e->getMessage()
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
            $activity = Activity::find($id);

            if (!$activity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aktivitas tidak ditemukan'
                ], 404);
            }

            $activity->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Aktivitas berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus aktivitas: ' . $e->getMessage()
            ], 500);
        }
    }
}
