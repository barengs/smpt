<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Fetch all students from the database
            $students = Student::with(['program', 'hostel', 'parents'])->orderBy('created_at', 'desc')->get();

            return new StudentResource('data ditemukan', $students, 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch students: ' . $e->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No students found',
            ], 404);
            //throw $th;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $student = Student::with(['program', 'hostel', 'parents'])->findOrFail($id);
            return new StudentResource('data ditemukan', $student, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch student: ' . $th->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Find the student
            $student = Student::findOrFail($id);

            // Validate the request
            $validator = Validator::make($request->all(), [
                'parent_id' => 'nullable|string',
                'nis' => 'required|string|max:20|unique:students,nis,' . $id,
                'period' => 'nullable|string|max:10',
                'nik' => 'nullable|string|max:16|unique:students,nik,' . $id,
                'kk' => 'nullable|string|max:16',
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'gender' => 'required|in:L,P',
                'address' => 'nullable|string',
                'born_in' => 'nullable|string|max:255',
                'born_at' => 'nullable|date',
                'last_education' => 'nullable|string|max:255',
                'village_id' => 'nullable|exists:villages,id',
                'village' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'phone' => 'nullable|string|max:15',
                'hostel_id' => 'nullable|exists:hostels,id',
                'program_id' => 'required|exists:programs,id',
                'status' => 'required|in:Tidak Aktif,Aktif,Tugas,Lulus,Dikeluarkan',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle photo upload with Intervention Image
            $photoPath = $student->photo; // Keep existing photo by default
            if ($request->hasFile('photo')) {
                // Delete old photo if it exists
                if ($student->photo && Storage::disk('public')->exists('students/photos/' . $student->photo)) {
                    Storage::disk('public')->delete('students/photos/' . $student->photo);
                }

                // Upload and resize new photo using Intervention Image
                $image = new ImageManager(new Driver());
                $photo = $request->file('photo');
                $filename = time() . '_' . $photo->getClientOriginalName();

                $resizedImage = $image->read($photo->getRealPath());
                // Resize image to a maximum of 800x800 while preserving aspect ratio
                $resizedImage->cover(800, 800, 'center');
                Storage::disk('public')->put('students/photos/' . $filename, (string) $resizedImage->encode());

                $photoPath = 'students/photos/' . $filename;
            }

            // Update student data
            $student->update(array_merge($request->except('photo'), ['photo' => $photoPath]));

            // Load updated data with relationships
            $updatedStudent = Student::with(['program', 'hostel', 'parents'])->findOrFail($id);

            return new StudentResource('Data siswa berhasil diperbarui', $updatedStudent, 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found',
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update student: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
