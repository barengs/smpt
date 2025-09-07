<?php

namespace App\Http\Controllers\Api\Master;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ChartOfAccountResource;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $coa = ChartOfAccount::with('children')->get();
            $tree = $this->buildTree($coa->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Data chart of account berhasil diambil',
                'data' => $tree
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data chart of account',
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
                'coa_code' => 'required|string|unique:chart_of_accounts,coa_code',
                'account_name' => 'required|string',
                'account_type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
                'parent_coa_code' => 'nullable|string|exists:chart_of_accounts,coa_code',
                'level' => 'required|in:header,subheader,detail',
                'is_postable' => 'required|boolean',
                'is_active' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $coa = ChartOfAccount::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chart of account berhasil ditambahkan',
                'data' => $coa
            ], 201);
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
                'message' => 'Gagal menambahkan chart of account',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan chart of account',
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
            $coa = ChartOfAccount::with('children')->find($id);

            if (!$coa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chart of account tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data chart of account berhasil diambil',
                'data' => $coa
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data chart of account',
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
            $coa = ChartOfAccount::find($id);

            if (!$coa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chart of account tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'account_name' => 'required|string',
                'account_type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
                'parent_coa_code' => 'nullable|string|exists:chart_of_accounts,coa_code',
                'level' => 'required|in:header,subheader,detail',
                'is_postable' => 'required|boolean',
                'is_active' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $coa->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chart of account berhasil diperbarui',
                'data' => $coa
            ], 200);
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
                'message' => 'Gagal memperbarui chart of account',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui chart of account',
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
            $coa = ChartOfAccount::find($id);

            if (!$coa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chart of account tidak ditemukan'
                ], 404);
            }

            // Check if this COA has children
            if ($coa->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus chart of account yang memiliki anak'
                ], 400);
            }

            DB::beginTransaction();

            $coa->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Chart of account berhasil dihapus'
            ], 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus chart of account',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus chart of account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build a tree structure from chart of accounts data
     *
     * @param array $data
     * @param string|null $parentCoaCode
     * @return array
     */
    private function buildTree(array $data, ?string $parentCoaCode = null): array
    {
        $tree = [];

        foreach ($data as $item) {
            if ($item['parent_coa_code'] === $parentCoaCode) {
                $children = $this->buildTree($data, $item['coa_code']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }
}
