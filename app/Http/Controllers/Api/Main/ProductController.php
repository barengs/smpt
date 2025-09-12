<?php

namespace App\Http\Controllers\Api\Main;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Fetch all products from the database
            $products = Product::all();

            return new ProductResource('data ditemukan', $products, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch products: ' . $e->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No products found',
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_code' => 'required|string|max:20|unique:products,product_code',
                'product_name' => 'required|string|max:100',
                'product_type' => 'required|in:Tabungan,Deposito,Pinjaman',
                'interest_rate' => 'nullable|numeric|min:0|max:100',
                'admin_fee' => 'nullable|numeric|min:0',
                'opening_fee' => 'required|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product = Product::create($request->all());

            return new ProductResource('Product created successfully', $product, 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            return new ProductResource('data ditemukan', $product, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch product: ' . $th->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'product_code' => 'sometimes|required|string|max:20|unique:products,product_code,' . $id,
                'product_name' => 'sometimes|required|string|max:100',
                'product_type' => 'sometimes|required|in:Tabungan,Deposito,Pinjaman',
                'interest_rate' => 'nullable|numeric|min:0|max:100',
                'admin_fee' => 'nullable|numeric|min:0',
                'opening_fee' => 'nullable|numeric|min:0',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product->update($request->all());

            return new ProductResource('Product updated successfully', $product, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update product: ' . $th->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete product: ' . $th->getMessage(),
            ], 500);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }
    }
}
