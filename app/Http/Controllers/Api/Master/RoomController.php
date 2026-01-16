<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $rooms = Room::with('hostel')->orderByDesc('id')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data ruangan berhasil diambil',
                'data' => $rooms
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data ruangan',
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
                'hostel_id' => 'required|exists:hostels,id',
                'capacity' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $room = Room::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil ditambahkan',
                'data' => $room
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
                'message' => 'Gagal menambahkan ruangan',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan ruangan',
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
            $room = Room::with('hostel')->find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data ruangan berhasil diambil',
                'data' => $room
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data ruangan',
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
            $room = Room::find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'hostel_id' => 'required|exists:hostels,id',
                'capacity' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $room->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil diperbarui',
                'data' => $room
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
                'message' => 'Gagal memperbarui ruangan',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui ruangan',
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
            $room = Room::find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan tidak ditemukan'
                ], 404);
            }

            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus ruangan',
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
            $room = Room::withTrashed()->find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan tidak ditemukan'
                ], 404);
            }

            if (!$room->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruangan tidak dalam keadaan terhapus'
                ], 400);
            }

            $room->restore();

            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil dipulihkan',
                'data' => $room
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan ruangan',
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
            $rooms = Room::onlyTrashed()->with('hostel')->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Data ruangan terhapus berhasil diambil',
                'data' => $rooms
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data ruangan terhapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export room data to Excel (Readable)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RoomReadableExport, 'laporan_ruangan_' . date('Y-m-d_H-i-s') . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Backup room data to CSV (Raw)
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function backup()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RoomBackupExport, 'backup_ruangan_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
