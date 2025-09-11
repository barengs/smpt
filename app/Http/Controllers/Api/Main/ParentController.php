<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\ParentProfile;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ParentResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
                'occupation' => $request->occupation,
                'education' => $request->education,
                'user_id' => $user->id,
                'photo' => $request->photo,
                'photo_path' => $request->photo_path,
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
            $parent = User::whereHas('parent')->where('id', $id)->with(['parent', 'roles'])->firstOrFail();

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
        //
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
}
