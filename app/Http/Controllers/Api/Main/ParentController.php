<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\ParentProfile;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ParentResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParentsImport;
use App\Exports\ParentTemplateExport;
use Exception;

/**
 * @tags Parent Management
 *
 * APIs for managing parent/guardian profiles including CRUD operations,
 * batch Excel/CSV imports with automatic user account creation.
 */
class ParentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = User::whereHas('parent')->with(['parent', 'roles'])->get();
            return new ParentResource('data ditemukan', $user, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('data tidak ada', 404);
        } catch (\Throwable $th) {
            return response()->json('An error occurred: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'nik' => 'required|string|unique:parant_profiles,nik|max:16',
            'kk' => 'required|string|max:16',
            'gender' => 'required|in:L,P',
            'parent_as' => 'required|in:ayah,ibu',
            'card_address' => 'nullable|string|max:255',
            'domicile_address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'occupation' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // run transaction
        DB::beginTransaction();

        try {
            // cek if nik dan kk sudah ada
            $ifKKExist = ParentProfile::where('kk', $request->kk)->first();
            if ($ifKKExist) {
                return new ParentResource('kk sudah ada', $ifKKExist->kk, 409);
            }

            $ifExist = ParentProfile::where('nik', $request->nik)->first();
            if ($ifExist) {
                return new ParentResource('nik sudah ada', $ifExist, 409);
            }

            $user = User::create([
                'name' => $request->first_name,
                'email' => $request->email,
                'password' => bcrypt($request->nik),
            ]);

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = time() . '_' . $photo->getClientOriginalName();
                $photoPath = $photo->storeAs('parents/photos', $filename, 'public');
            }

            $user->parentProfile()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'nik' => $request->nik,
                'kk' => $request->kk,
                'gender' => $request->gender,
                'parent_as' => $request->parent_as,
                'card_address' => $request->card_address,
                'domicile_address' => $request->domicile_address,
                'phone' => $request->phone,
                'email' => $request->email,
                'occupation_id' => $request->occupation,
                'education_id' => $request->education,
                'user_id' => $user->id,
                'photo' => $photoPath,
            ]);

            $user->syncRoles('user');

            DB::commit();

            return new ParentResource('data berhasil ditambahkan', $user, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json('data tidak ditemukan', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json('terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $parent = User::whereHas('parent')
                ->where('id', $id)
                ->with(['parent.education', 'parent.occupation', 'roles'])
                ->firstOrFail();

            $students = Student::where('parent_id', $parent->parent->nik)->get();

            $parent->students = $students;

            return new ParentResource('data ditemukan', $parent, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('data tidak ditemukan', 404);
        } catch (\Throwable $th) {
            return response()->json('An error occurred: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'nik' => 'required|string|max:16',
            'kk' => 'required|string|max:16',
            'gender' => 'required|in:L,P',
            'parent_as' => 'required|in:ayah,ibu',
            'card_address' => 'nullable|string|max:255',
            'domicile_address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'email' => 'nullable|max:255',
            'occupation_id' => 'nullable',
            'education_id' => 'nullable',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Start transaction
        DB::beginTransaction();

        try {
            // Find the user and parent profile
            $user = User::whereHas('parent')->with('parent')->where('id', $id)->firstOrFail();
            $parentProfile = $user->parent;
            $oldNik = $parentProfile->nik;
            $newNik = $request->nik;

            // Check if KK already exists for another parent
            $ifKKExist = ParentProfile::where('kk', $request->kk)->where('id', '!=', $parentProfile->id)->first();
            if ($ifKKExist) {
                return new ParentResource('KK sudah digunakan oleh orang tua lain', null, 409);
            }

            // Check if NIK already exists for another parent
            $ifNikExist = ParentProfile::where('nik', $request->nik)->where('id', '!=', $parentProfile->id)->first();
            if ($ifNikExist) {
                return new ParentResource('NIK sudah digunakan oleh orang tua lain', null, 409);
            }

            // Determine Email Logic
            // If the current User email matches the OLD NIK, it means they are using NIK as username.
            // We should update it to the NEW NIK.
            if ($user->email === $oldNik) {
                 // Check if new NIK is already used as email by another user
                 if (User::where('email', $newNik)->where('id', '!=', $user->id)->exists()) {
                     return response()->json(['message' => 'NIK (Username) sudah digunakan oleh pengguna lain'], 409);
                 }
                 $user->email = $newNik;
            }

            // If request has explicit email, it overrides (or updates if they switched from NIK to email)
            if ($request->filled('email') && $request->email !== $user->email) {
                 // Check uniqueness
                 if (User::where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
                     return response()->json(['message' => 'Email sudah digunakan oleh pengguna lain'], 409);
                 }
                 $user->email = $request->email;
            }

            $user->name = $request->first_name;
            $user->save();

            // Handle photo upload
            $photoPath = $parentProfile->photo; // Keep existing photo by default
            if ($request->hasFile('photo')) {
                // Delete old photo if it exists
                if ($parentProfile->photo && Storage::disk('public')->exists($parentProfile->photo)) {
                    Storage::disk('public')->delete($parentProfile->photo);
                }

                // Upload new photo
                $photo = $request->file('photo');
                $filename = time() . '_' . $photo->getClientOriginalName();
                $photoPath = $photo->storeAs('parents/photos', $filename, 'public');
            }

            // Update parent profile
            $parentProfile->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'nik' => $request->nik,
                'kk' => $request->kk,
                'gender' => $request->gender,
                'parent_as' => $request->parent_as,
                'card_address' => $request->card_address,
                'domicile_address' => $request->domicile_address,
                'phone' => $request->phone,
                'email' => $request->email, // This is contact email in profile, separate from User login
                'occupation_id' => $request->occupation_id,
                'education_id' => $request->education_id,
                'photo' => $photoPath,
            ]);

            DB::commit();

            // Load updated data with relationships
            $updatedUser = User::whereHas('parent')
                ->where('id', $id)
                ->with(['parent.education', 'parent.occupation', 'roles'])
                ->first();

            return new ParentResource('Data orang tua berhasil diperbarui', $updatedUser, 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json('Data orang tua tidak ditemukan', 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getByNik($nik)
    {
        try {
            $parent = ParentProfile::where('nik', $nik)->first();
            // dd($parent);
            return response()->json([
                'status' => 'success',
                'data' => $parent,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json('An error occurred: ' . $th->getMessage(), 500);
        }
    }

    /**
     * Import parents from Excel or CSV file
     *
     * This endpoint allows batch importing of parent data with automatic user account creation.
     * For each parent imported, the system automatically:
     * - Creates a user account with NIK as email/username
     * - Sets default password to "password"
     * - Assigns "user" role
     * - Validates NIK and KK uniqueness
     * - Handles numeric string fields correctly
     * - Uses database transactions for data integrity
     *
     * @param Request $request
     * @bodyParam file file required The Excel or CSV file containing parent data. Max size: 10MB. Allowed formats: .xlsx, .xls, .csv. Example: parent_data.xlsx
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Import completed",
     *   "data": {
     *     "success_count": 45,
     *     "failure_count": 5,
     *     "total": 50,
     *     "info": "User accounts created with NIK as email and default password: \"password\"",
     *     "errors": ["Row 3: NIK 12345 already exists - skipped"],
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
     *   "error": "Database transaction failed"
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
            $import = new ParentsImport();

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
                    'info' => 'User accounts created with NIK as email and default password: "password"'
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
     * Download Excel template for parent import
     *
     * Downloads a pre-formatted Excel template file (.xlsx) with:
     * - All required and optional column headers
     * - One sample row with example data
     * - Bold headers and properly sized columns
     * - Ready to fill and upload for batch import
     *
     * The template includes columns: nik, kk, first_name, last_name, gender, parent_as,
     * card_address, domicile_address, phone, email, occupation_id, education_id.
     *
     * Important Notes:
     * - NIK will be used as the email/username for the auto-created user account
     * - Default password "password" will be set for all imported parents
     * - "user" role will be automatically assigned
     * - Recommend implementing forced password change on first login
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
                new ParentTemplateExport(),
                'parent_import_template.xlsx'
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
