<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\HostelRequest;
use App\Http\Resources\HostelResource;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HostelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $hostels = Hostel::with('program')->paginate(10);
            return response()->json(new HostelResource('Data asrama berhasil diambil', $hostels, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Gagal mengambil data asrama', null, 500), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HostelRequest $request): JsonResponse
    {
        try {
            $hostel = Hostel::create($request->validated());
            return response()->json(new HostelResource('Asrama berhasil ditambahkan', $hostel, 201), 201);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Gagal menambahkan asrama', null, 500), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $hostel = Hostel::findOrFail($id);
            return response()->json(new HostelResource('Data asrama berhasil diambil', $hostel, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Asrama tidak ditemukan', null, 404), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HostelRequest $request, string $id): JsonResponse
    {
        try {

            $hostel = Hostel::findOrFail($id);
            $hostel->update([
                'name' => $request->name,
                'program_id' => $request->program_id ?? $hostel->program_id,
                'description' => $request->description,
                'capacity' => $request->capacity ?? $hostel->capacity, // Keep existing capacity if not provided
            ]);
            return response()->json(new HostelResource('Asrama berhasil diperbarui', $hostel, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Gagal memperbarui asrama', null, 500), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $hostel = Hostel::findOrFail($id);
            $hostel->delete();
            return response()->json(new HostelResource('Asrama berhasil dihapus', null, 200), 200);
        } catch (\Exception $e) {
            return response()->json(new HostelResource('Gagal menghapus asrama', null, 500), 500);
        }
    }
}
