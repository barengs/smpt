<?php

namespace App\Http\Controllers\Api\Main;

use App\Http\Resources\StaffResource;
use Exception;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = User::whereHas('staff')->with(['staff', 'roles'])->get();

            return new StaffResource('Data berhasil diambil', $query, 200);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while fetching staff members',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching staff members',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'nik' => 'nullable|string|max:20|unique:staff,nik',
                'email' => 'required|email|unique:staff,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'zip_code' => 'nullable|string|max:10',
                'photo' => 'nullable|string|max:255',
                'status' => 'required|in:Aktif,Tidak Aktif',
                'role' => 'required',
            ]);

            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            // Check if user already has a staff record
            $existingStaff = Staff::where('user_id', $request->user_id)->first();
            if ($existingStaff) {
                return response()->json([
                    'error' => 'User already has a staff record'
                ], 409);
            }

            DB::beginTransaction();

            // create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->syncRoles($request->role);

            // Create the staff
            $staff = Staff::create([
                'user_id' => $user->id,
                'code' => $this->generateStaffCode(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'nik' => $request->nik,
                'zip_code' => $request->zip_code,
                'status' => $request->status,
            ]);

            DB::commit();

            // Load the user relationship
            $staff->load('user');

            return new StaffResource('Data berhasil di simpan', $staff, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Database error occurred while creating staff member',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred while creating staff member',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $staff = Staff::with('user')->find($id);

            if (!$staff) {
                return response()->json([
                    'error' => 'Staff not found'
                ], 404);
            }

            return response()->json($staff);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while fetching staff member',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching staff member',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'error' => 'Staff not found'
                ], 404);
            }

            // Validate the request data
            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|required|exists:users,id',
                'code' => 'sometimes|required|unique:staff,code,' . $id,
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'nik' => 'nullable|string|max:20|unique:staff,nik,' . $id,
                'email' => 'sometimes|required|email|unique:staff,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'zip_code' => 'nullable|string|max:10',
                'photo' => 'nullable|string|max:255',
                'status' => 'sometimes|required|in:Aktif,Tidak Aktif',
            ]);

            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            // Check if changing user_id and if that user already has a staff record
            if ($request->has('user_id') && $request->user_id != $staff->user_id) {
                $existingStaff = Staff::where('user_id', $request->user_id)->first();
                if ($existingStaff) {
                    return response()->json([
                        'error' => 'User already has a staff record'
                    ], 409);
                }
            }

            // Update the staff
            $staff->update($request->all());

            // Load the user relationship
            $staff->load('user');

            return response()->json($staff);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while updating staff member',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating staff member',
                'message' => $e->getMessage()
            ], 500);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'error' => 'Staff not found'
                ], 404);
            }

            // Delete the staff (soft delete)
            $staff->delete();

            return response()->json([
                'message' => 'Staff deleted successfully'
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while deleting staff member',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting staff member',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted staff member.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        try {
            $staff = Staff::withTrashed()->find($id);

            if (!$staff) {
                return response()->json([
                    'error' => 'Staff not found'
                ], 404);
            }

            if (!$staff->trashed()) {
                return response()->json([
                    'error' => 'Staff is not deleted'
                ], 400);
            }

            // Restore the staff
            $staff->restore();

            return response()->json($staff);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while restoring staff member',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while restoring staff member',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all soft deleted staff members.
     *
     * @return \Illuminate\Http\JsonResponse
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

            return response()->json($staff);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while fetching trashed staff members',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching trashed staff members',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get staff by user ID.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByUserId($userId)
    {
        try {
            // Validate user ID
            if (!is_numeric($userId)) {
                return response()->json([
                    'error' => 'Invalid user ID'
                ], 400);
            }

            $staff = Staff::with('user')->where('user_id', $userId)->first();

            if (!$staff) {
                return response()->json([
                    'error' => 'Staff not found for this user'
                ], 404);
            }

            return response()->json($staff);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while fetching staff by user ID',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching staff by user ID',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete staff members.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:staff,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            $ids = $request->input('ids');
            $deletedCount = Staff::whereIn('id', $ids)->delete();

            return response()->json([
                'message' => $deletedCount . ' staff members deleted successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while bulk deleting staff members',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while bulk deleting staff members',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk restore staff members.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkRestore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:staff,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            $ids = $request->input('ids');
            $restoredCount = Staff::withTrashed()->whereIn('id', $ids)->restore();

            return response()->json([
                'message' => $restoredCount . ' staff members restored successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while bulk restoring staff members',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while bulk restoring staff members',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update staff status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $staff = Staff::find($id);

            if (!$staff) {
                return response()->json([
                    'error' => 'Staff not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:Aktif,Tidak Aktif',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            $staff->update(['status' => $request->status]);

            return response()->json([
                'message' => 'Staff status updated successfully',
                'staff' => $staff
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while updating staff status',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating staff status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get staff statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $total = Staff::count();
            $active = Staff::where('status', 'Aktif')->count();
            $inactive = Staff::where('status', 'Tidak Aktif')->count();
            $trashed = Staff::onlyTrashed()->count();

            return response()->json([
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'trashed' => $trashed
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while fetching staff statistics',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching staff statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete staff members (permanently remove from database).
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        try {
            $staff = Staff::withTrashed()->find($id);

            if (!$staff) {
                return response()->json([
                    'error' => 'Staff not found'
                ], 404);
            }

            // Force delete the staff
            $staff->forceDelete();

            return response()->json([
                'message' => 'Staff permanently deleted successfully'
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while force deleting staff member',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while force deleting staff member',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk force delete staff members.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkForceDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'exists:staff,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()
                ], 422);
            }

            $ids = $request->input('ids');
            $deletedCount = Staff::withTrashed()->whereIn('id', $ids)->forceDelete();

            return response()->json([
                'message' => $deletedCount . ' staff members permanently deleted successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error occurred while bulk force deleting staff members',
                'message' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while bulk force deleting staff members',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
