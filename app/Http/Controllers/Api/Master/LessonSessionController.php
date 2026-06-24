<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\LessonSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = LessonSession::query();
        
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }
        
        $query->orderBy('order', 'asc')->orderBy('id', 'asc');
        
        return response()->json([
            'success' => true,
            'message' => 'Data sesi pelajaran berhasil diambil',
            'data' => $query->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $session = LessonSession::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sesi pelajaran berhasil ditambahkan',
            'data' => $session
        ], 201);
    }

    public function show(LessonSession $lessonSession)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail sesi pelajaran',
            'data' => $lessonSession
        ]);
    }

    public function update(Request $request, LessonSession $lessonSession)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $lessonSession->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sesi pelajaran berhasil diperbarui',
            'data' => $lessonSession
        ]);
    }

    public function destroy(LessonSession $lessonSession)
    {
        $lessonSession->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesi pelajaran berhasil dihapus'
        ]);
    }
}
