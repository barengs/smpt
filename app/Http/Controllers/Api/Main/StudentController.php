<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\Student;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentTemplateExport;

/**
 * @tags Student Management
 *
 * APIs for managing student records including CRUD operations, photo uploads,
 * room assignments, and batch Excel/CSV imports.
 */
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

            // Attach current room for each student
            $students->each(function ($student) {
                $currentRoom = DB::table('student_room_assignments as sra')
                    ->join('rooms as r', 'r.id', '=', 'sra.room_id')
                    ->join('hostels as h', 'h.id', '=', 'r.hostel_id')
                    ->leftJoin('academic_years as ay', 'ay.id', '=', 'sra.academic_year_id')
                    ->where('sra.student_id', $student->id)
                    ->where('sra.is_active', true)
                    ->select([
                        'sra.id', 'sra.start_date', 'sra.end_date', 'sra.notes',
                        'r.id as room_id', 'r.name as room_name', 'r.capacity as room_capacity',
                        'h.id as hostel_id', 'h.name as hostel_name',
                        'ay.id as academic_year_id', 'ay.year as academic_year',
                    ])
                    ->first();
                $student->current_room = $currentRoom;
            });

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

            // Attach current room
            $currentRoom = DB::table('student_room_assignments as sra')
                ->join('rooms as r', 'r.id', '=', 'sra.room_id')
                ->join('hostels as h', 'h.id', '=', 'r.hostel_id')
                ->leftJoin('academic_years as ay', 'ay.id', '=', 'sra.academic_year_id')
                ->where('sra.student_id', $student->id)
                ->where('sra.is_active', true)
                ->select([
                    'sra.id', 'sra.start_date', 'sra.end_date', 'sra.notes',
                    'r.id as room_id', 'r.name as room_name', 'r.capacity as room_capacity',
                    'h.id as hostel_id', 'h.name as hostel_name',
                    'ay.id as academic_year_id', 'ay.year as academic_year',
                ])
                ->first();
            $student->current_room = $currentRoom;

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
                'village_id' => 'nullable|exists:indonesia_villages,id',
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

    /**
     * Assign or move student to a room (tracks history)
     */
    public function assignRoom(Request $request, string $id)
    {
        try {
            $student = Student::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'room_id' => 'required|exists:rooms,id',
                'academic_year_id' => 'nullable|exists:academic_years,id',
                'start_date' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $room = Room::findOrFail($request->room_id);

            DB::beginTransaction();

            // Deactivate previous active assignment
            DB::table('student_room_assignments')
                ->where('student_id', $student->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'end_date' => $request->start_date,
                ]);

            // Create new assignment
            $assignmentId = DB::table('student_room_assignments')->insertGetId([
                'student_id' => $student->id,
                'room_id' => $room->id,
                'academic_year_id' => $request->academic_year_id,
                'start_date' => $request->start_date,
                'end_date' => null,
                'is_active' => true,
                'notes' => $request->notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update student's hostel based on room
            $student->update([
                'hostel_id' => $room->hostel_id,
            ]);

            DB::commit();

            $assignment = DB::table('student_room_assignments')->where('id', $assignmentId)->first();

            return response()->json([
                'success' => true,
                'message' => 'Penempatan kamar berhasil',
                'data' => $assignment
            ], 201);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Siswa atau kamar tidak ditemukan'
            ], 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan penempatan kamar',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Riwayat penempatan kamar siswa
     */
    public function roomHistory(string $id)
    {
        try {
            $student = Student::findOrFail($id);

            $history = DB::table('student_room_assignments as sra')
                ->join('rooms as r', 'r.id', '=', 'sra.room_id')
                ->join('hostels as h', 'h.id', '=', 'r.hostel_id')
                ->leftJoin('academic_years as ay', 'ay.id', '=', 'sra.academic_year_id')
                ->where('sra.student_id', $student->id)
                ->orderByDesc('sra.start_date')
                ->select([
                    'sra.id', 'sra.start_date', 'sra.end_date', 'sra.is_active', 'sra.notes',
                    'r.id as room_id', 'r.name as room_name',
                    'h.id as hostel_id', 'h.name as hostel_name',
                    'ay.id as academic_year_id', 'ay.year as academic_year',
                ])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat kamar siswa berhasil diambil',
                'data' => $history
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Student Photo
     *
     * Upload or replace the profile photo for a specific student.
     * The system automatically converts any uploaded image (JPEG, PNG, GIF) to **WebP** format
     * and resizes it to 800x800px.
     *
     * @param Request $request
     * @param string $id Student ID
     *
     * @bodyParam photo file required The image file to upload. Max size: 2MB. Allowed formats: jpeg, png, jpg, gif.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Foto berhasil diperbarui",
     *   "data": {
     *     "id": 1,
     *     "first_name": "Ahmad",
     *     "photo": "students/photos/1700000000_profile.webp"
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "message": "Validasi gagal",
     *   "errors": {
     *     "photo": ["The photo field must be an image."]
     *   }
     * }
     */
    public function updatePhoto(Request $request, string $id)
    {
        try {
            $student = Student::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->hasFile('photo')) {
                // Delete old photo if it exists
                if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                    Storage::disk('public')->delete($student->photo);
                }

                // Upload and resize new photo using Intervention Image
                $image = new ImageManager(new Driver());
                $photo = $request->file('photo');
                
                // Generate filename with .webp extension
                $filename = time() . '_' . pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';

                $resizedImage = $image->read($photo->getRealPath());
                // Resize image to a maximum of 800x800 while preserving aspect ratio
                $resizedImage->cover(800, 800, 'center');
                
                // Store in specific directory as WebP
                $path = 'students/photos/' . $filename;
                Storage::disk('public')->put($path, (string) $resizedImage->toWebp(80));

                // Update database
                $student->update(['photo' => $path]);

                return response()->json([
                    'success' => true,
                    'message' => 'Foto berhasil diperbarui',
                    'data' => $student
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada file foto yang diunggah'
            ], 400);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui foto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import students from Excel or CSV file
     *
     * This endpoint allows batch importing of student data from Excel (.xlsx, .xls) or CSV files.
     * The import automatically validates data, checks for duplicate NIS, handles numeric string fields,
     * and records the authenticated staff member who performed the import.
     *
     * @param Request $request
     * @bodyParam file file required The Excel or CSV file containing student data. Max size: 10MB. Allowed formats: .xlsx, .xls, .csv. Example: student_data.xlsx
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Import completed",
     *   "data": {
     *     "success_count": 95,
     *     "failure_count": 5,
     *     "total": 100,
     *     "errors": ["Row 5: NIS 12345 already exists - skipped"],
     *     "total_errors": 5
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "message": "Validasi gagal",
     *   "errors": {"file": ["The file field is required."]}
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Gagal mengimpor data",
     *   "error": "Database connection error"
     * }
     */
    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            $import = new StudentsImport();

            // Import the file
            Excel::import($import, $file);

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            $failureCount = $import->getFailureCount();

            // Prepare response
            $response = [
                'success' => true,
                'message' => 'Import completed',
                'data' => [
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                    'total' => $successCount + $failureCount,
                ]
            ];

            if (count($errors) > 0) {
                $response['data']['errors'] = array_slice($errors, 0, 50); // Limit to first 50 errors
                $response['data']['total_errors'] = count($errors);
                $response['message'] = 'Import completed with some errors';
            }

            return response()->json($response, 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel template for student import
     *
     * Downloads a pre-formatted Excel template file (.xlsx) with:
     * - All required and optional column headers
     * - One sample row with example data
     * - Bold headers and properly sized columns
     * - Ready to fill and upload for batch import
     *
     * The template includes columns: nis, first_name, last_name, gender, program_id, parent_id,
     * period, nik, kk, address, born_in, born_at, last_education, village_id, village, district,
     * postal_code, phone, hostel_id, status.
     *
     * Note: The user_id field is NOT included in the template as it is automatically assigned
     * from the authenticated staff member during import.
     *
     * @response 200 Binary file download (application/vnd.openxmlformats-officedocument.spreadsheetml.sheet)
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Gagal mengunduh template",
     *   "error": "File generation error"
     * }
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(
                new StudentTemplateExport(),
                'student_import_template.xlsx'
            );
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh template',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
