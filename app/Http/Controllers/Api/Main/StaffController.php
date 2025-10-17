<?php

namespace App\Http\Controllers\Api\Main;

use Exception;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManager;
use App\Http\Resources\StaffResource;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Imagick\Driver;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\StaffResource
     */
    public function index(Request $request)
    {
        try {
            $query = User::whereHas('staff')->with(['staff', 'roles'])->get();

            return new StaffResource('Data berhasil diambil', $query, 200);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while fetching staff members', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while fetching staff members', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\StaffResource
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
            'email' => 'required|email|unique:staff,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:20|unique:staff,nik',
            'nip' => 'nullable|string|max:20|unique:staff,nip',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'zip_code' => 'nullable|string|max:10',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'roles' => 'required',
        ]);

        try {

            // Check if user already has a staff record
            // $existingStaff = Staff::where('user_id', $request->user_id)->first();
            // if ($existingStaff) {
            //     return new StaffResource('User already has a staff record', null, 409);
            // }

            DB::beginTransaction();

            // create user
            $user = User::create([
                'name' => $request->username,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : Hash::make('password'),
            ]);

            $user->syncRoles($request->roles);

            if ($request->hasFile('photo')) {
                $image = new ImageManager(new Driver());
                $timestamp = now()->timestamp;
                $fileName = $timestamp . '_' . $request->file('photo')->getClientOriginalName();

                $largeImage = $image->read($request->file('photo')->getRealPath());
                $largeImage->cover(512, 512);
                Storage::disk('public')->put('uploads/logos/large/' . $fileName, (string) $largeImage->encode());
            }

            // Create the staff
            $staff = Staff::create([
                'user_id' => $user->id,
                'code' => $this->generateCode(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'nik' => $request->nik,
                'nip' => $request->nip,
                'gender' => $request->gender,
                'village_id' => $request->village_id,
                'zip_code' => $request->zip_code,
                'marital_status' => $request->marital_status ?? 'Belum Menikah',
                'status' => $request->status ?? 'Aktif',
                'job_id' => $request->job_id,
            ]);

            DB::commit();

            // Load the user relationship
            $staff->load('user');

            return new StaffResource('Data berhasil di simpan', $staff, 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            return new StaffResource('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            DB::rollBack();
            return new StaffResource('Database error occurred while creating staff member', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return new StaffResource('An error occurred while creating staff member', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \App\Http\Resources\StaffResource
     */
    public function show($id)
    {
        try {
            // Load staff with user and roles information
            $staff = Staff::with(['user.roles'])->find($id);

            if (!$staff) {
                return new StaffResource('Staff not found', null, 404);
            }

            return new StaffResource('Data berhasil diambil', $staff, 200);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while fetching staff member', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while fetching staff member', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \App\Http\Resources\StaffResource
     */
    public function update(Request $request, $id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return new StaffResource('Staff not found', null, 404);
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|required|exists:users,id',
                'code' => 'sometimes|required|unique:staff,code,',
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'nip' => 'nullable|string|max:20|unique:staff,nip,',
                'nik' => 'nullable|string|max:20|unique:staff,nik,',
                'email' => 'sometimes|required|email|unique:staff,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'zip_code' => 'nullable|string|max:10',
                'photo' => 'nullable|string|max:255',
                'marital_status' => 'sometimes|required|in:Single,Married,Divorced,Widowed',
                'status' => 'sometimes|required|in:Aktif,Tidak Aktif',
            ]);

            // Return validation errors if any
            if ($validator->fails()) {
                return new StaffResource('Validation failed', $validator->errors(), 422);
            }

            // Check if changing user_id and if that user already has a staff record
            if ($request->has('user_id') && $request->user_id != $staff->user_id) {
                $existingStaff = Staff::where('user_id', $request->user_id)->first();
                if ($existingStaff) {
                    return new StaffResource('User already has a staff record', null, 409);
                }
            }

            // Update the staff
            $staff->update($request->all());

            // Load the user relationship
            $staff->load('user');

            return new StaffResource('Data berhasil di update', $staff, 200);
        } catch (ValidationException $e) {
            return new StaffResource('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while updating staff member', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while updating staff member', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate a new staff code
     *
     * Generate white standard code 'SP0000'
     */
    public function generateCode()
    {
        $lastStaff = Staff::orderBy('id', 'desc')->first();
        $code = 'SP' . str_pad(($lastStaff ? $lastStaff->id + 1 : 1), 4, '0', STR_PAD_LEFT);
        return $code;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \App\Http\Resources\StaffResource
     */
    public function destroy($id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return new StaffResource('Staff not found', null, 404);
            }

            // Delete the staff (soft delete)
            $staff->delete();

            return new StaffResource('Staff deleted successfully', null, 200);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while deleting staff member', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while deleting staff member', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore a soft deleted staff member.
     *
     * @param  string  $id
     * @return \App\Http\Resources\StaffResource
     */
    public function restore($id)
    {
        try {
            $staff = Staff::withTrashed()->find($id);

            if (!$staff) {
                return new StaffResource('Staff not found', null, 404);
            }

            if (!$staff->trashed()) {
                return new StaffResource('Staff is not deleted', null, 400);
            }

            // Restore the staff
            $staff->restore();

            return new StaffResource('Data berhasil di restore', $staff, 200);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while restoring staff member', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while restoring staff member', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all soft deleted staff members.
     *
     * @return \App\Http\Resources\StaffResource
     */
    public function trashed(Request $request)
    {
        try {
            $query = Staff::onlyTrashed()->with('user');

            // Search by name or email if provided
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Sort by column if provided
            $sortBy = $request->get('sort_by', 'deleted_at');
            $sortDirection = $request->get('sort_direction', 'desc');

            // Validate sort parameters
            $allowedSortColumns = ['first_name', 'last_name', 'email', 'status', 'created_at', 'updated_at', 'deleted_at'];
            $allowedSortDirections = ['asc', 'desc'];

            if (in_array($sortBy, $allowedSortColumns) && in_array($sortDirection, $allowedSortDirections)) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->orderBy('deleted_at', 'desc'); // Default sorting
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $staff = $query->paginate($perPage);

            return new StaffResource('Data berhasil diambil', $staff, 200);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while fetching trashed staff members', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while fetching trashed staff members', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get staff by user ID.
     *
     * @param  int  $userId
     * @return \App\Http\Resources\StaffResource
     */
    public function getByUserId($userId)
    {
        try {
            // Validate user ID
            if (!is_numeric($userId)) {
                return new StaffResource('Invalid user ID', null, 400);
            }

            $staff = Staff::with('user')->where('user_id', $userId)->first();

            if (!$staff) {
                return new StaffResource('Staff not found for this user', null, 404);
            }

            return new StaffResource('Data berhasil diambil', $staff, 200);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while fetching staff by user ID', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while fetching staff by user ID', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete staff members.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\StaffResource
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:staff,id'
            ]);

            if ($validator->fails()) {
                return new StaffResource('Validation failed', $validator->errors(), 422);
            }

            $ids = $request->input('ids');
            $deletedCount = Staff::whereIn('id', $ids)->delete();

            return new StaffResource($deletedCount . ' staff members deleted successfully', null, 200);
        } catch (ValidationException $e) {
            return new StaffResource('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while bulk deleting staff members', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while bulk deleting staff members', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk restore staff members.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\StaffResource
     */
    public function bulkRestore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:staff,id'
            ]);

            if ($validator->fails()) {
                return new StaffResource('Validation failed', $validator->errors(), 422);
            }

            $ids = $request->input('ids');
            $restoredCount = Staff::withTrashed()->whereIn('id', $ids)->restore();

            return new StaffResource($restoredCount . ' staff members restored successfully', null, 200);
        } catch (ValidationException $e) {
            return new StaffResource('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while bulk restoring staff members', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while bulk restoring staff members', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update staff status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \App\Http\Resources\StaffResource
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return new StaffResource('Staff not found', null, 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:Aktif,Tidak Aktif',
            ]);

            if ($validator->fails()) {
                return new StaffResource('Validation failed', $validator->errors(), 422);
            }

            $staff->update(['status' => $request->status]);

            return new StaffResource('Staff status updated successfully', $staff, 200);
        } catch (ValidationException $e) {
            return new StaffResource('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while updating staff status', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while updating staff status', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get staff statistics.
     *
     * @return \App\Http\Resources\StaffResource
     */
    public function statistics()
    {
        try {
            $total = Staff::count();
            $active = Staff::where('status', 'Aktif')->count();
            $inactive = Staff::where('status', 'Tidak Aktif')->count();
            $trashed = Staff::onlyTrashed()->count();

            $data = [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'trashed' => $trashed
            ];

            return new StaffResource('Data berhasil diambil', $data, 200);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while fetching staff statistics', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while fetching staff statistics', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Force delete staff members (permanently remove from database).
     *
     * @param  string  $id
     * @return \App\Http\Resources\StaffResource
     */
    public function forceDelete($id)
    {
        try {
            $staff = Staff::withTrashed()->find($id);

            if (!$staff) {
                return new StaffResource('Staff not found', null, 404);
            }

            // Force delete the staff
            $staff->forceDelete();

            return new StaffResource('Staff permanently deleted successfully', null, 200);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while force deleting staff member', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while force deleting staff member', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk force delete staff members.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\StaffResource
     */
    public function bulkForceDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:staff,id'
            ]);

            if ($validator->fails()) {
                return new StaffResource('Validation failed', $validator->errors(), 422);
            }

            $ids = $request->input('ids');
            $deletedCount = Staff::withTrashed()->whereIn('id', $ids)->forceDelete();

            return new StaffResource($deletedCount . ' staff members permanently deleted successfully', null, 200);
        } catch (ValidationException $e) {
            return new StaffResource('Validation failed', $e->errors(), 422);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while bulk force deleting staff members', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while bulk force deleting staff members', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get staff with specific roles (asatidz and walikelas only).
     */
    public function getStaffByRoles()
    {
        try {
            $data = User::whereHas('staff')
                ->where(function ($query) {
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'asatidz');
                    })->orWhereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'walikelas');
                    });
                })
                ->with(['staff', 'roles'])
                ->get();

            return new StaffResource('data ditemukan', $data, 200);
        } catch (Exception $e) {
            return response()->json('terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single staff member by ID with specific roles (asatidz and walikelas only).
     */
    public function getStaffByRolesById(string $id)
    {
        try {
            $data = User::whereHas('staff')
                ->where('id', $id)
                ->where(function ($query) {
                    $query->whereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'asatidz');
                    })->orWhereHas('roles', function ($subQuery) {
                        $subQuery->where('name', 'walikelas');
                    });
                })
                ->with(['staff', 'roles'])
                ->firstOrFail();

            return new StaffResource('data ditemukan', $data, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json('data tidak ditemukan', 404);
        } catch (Exception $e) {
            return response()->json('terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Check NIK and extract information like province, city, district, birth date, and gender.
     *
     * @param Request $request
     * @return \App\Http\Resources\StaffResource
     */
    public function checkNik(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'nik' => 'required|string|size:16',
            ]);

            if ($validator->fails()) {
                return new StaffResource('Validation failed', $validator->errors(), 422);
            }

            $nik = $request->input('nik');

            // Extract information from NIK
            $provinceCode = substr($nik, 0, 2);
            $cityCode = substr($nik, 0, 4);
            $districtCode = substr($nik, 0, 6);
            $birthDate = substr($nik, 6, 6);

            // Parse birth date (DDMMYY format for Indonesian NIK)
            $day = substr($birthDate, 0, 2);
            $month = substr($birthDate, 2, 2);
            $year = substr($birthDate, 4, 2);

            // Adjust day for gender (for females, 40 is added to the day)
            $adjustedDay = (int)$day;
            if ($adjustedDay > 40) {
                $adjustedDay = $adjustedDay - 40;
                $gender = 'Perempuan';
            } else {
                $gender = 'Laki-laki';
            }

            // Adjust year (assuming 00-30 is 2000-2030 and 31-99 is 1931-1999)
            $fullYear = (int)$year <= 30 ? "20{$year}" : "19{$year}";

            // Format birth date
            $formattedBirthDate = sprintf("%s-%s-%02d", $fullYear, $month, $adjustedDay);

            // Get region information
            $province = DB::table(config('laravolt.indonesia.table_prefix').'provinces')
                ->where('code', $provinceCode)
                ->first();

            $city = DB::table(config('laravolt.indonesia.table_prefix').'cities')
                ->where('code', $cityCode)
                ->first();

            $district = DB::table(config('laravolt.indonesia.table_prefix').'districts')
                ->where('code', $districtCode)
                ->first();

            // Prepare response data
            $data = [
                'nik' => $nik,
                'province' => $province ? $province->name : null,
                'city' => $city ? $city->name : null,
                'district' => $district ? $district->name : null,
                'birth_date' => $formattedBirthDate,
                'gender' => $gender,
                'province_code' => $provinceCode,
                'city_code' => $cityCode,
                'district_code' => $districtCode,
            ];

            return new StaffResource('NIK information retrieved successfully', $data, 200);
        } catch (QueryException $e) {
            return new StaffResource('Database error occurred while checking NIK', ['message' => $e->getMessage()], 500);
        } catch (Exception $e) {
            return new StaffResource('An error occurred while checking NIK', ['message' => $e->getMessage()], 500);
        }
    }
}
