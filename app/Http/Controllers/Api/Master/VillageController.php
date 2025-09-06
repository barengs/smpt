<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\Village;

class VillageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $page = $request->get('per_page', 15);
            $villages = Village::paginate($page);

            return response()->json([
                'status' => 'success',
                'data' => $villages
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Show by district code.
     */
    public function showByDistrict(Request $request, $id)
    {
        $villages = Village::where('district_code', $id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $villages
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function showByNik(string $id)
    {
        try {
            // trim nik 4 digit
            $nik = substr($id, 0, 6);
            $village = Village::where('district_code', $nik)->get();

            return response()->json([
                'status' => 'success',
                'data' => $village
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ]);
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
}
