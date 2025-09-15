<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\EducationTypeRequest;
use App\Http\Resources\EducationTypeResource;
use App\Models\EducationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EducationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $educationTypes = EducationType::all();
            return new EducationTypeResource('Education types retrieved successfully', $educationTypes, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving education types: ' . $e->getMessage());
            return new EducationTypeResource('Failed to retrieve education types', null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EducationTypeRequest $request)
    {
        try {
            $educationType = EducationType::create($request->validated());
            return new EducationTypeResource('Education type created successfully', $educationType, 201);
        } catch (\Exception $e) {
            Log::error('Error creating education type: ' . $e->getMessage());
            return new EducationTypeResource('Failed to create education type', null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $education_type)
    {
        try {
            $educationType = EducationType::findOrFail($education_type);
            return new EducationTypeResource('Education type retrieved successfully', $educationType, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new EducationTypeResource('Education type not found', null, 404);
        } catch (\Exception $e) {
            Log::error('Error retrieving education type: ' . $e->getMessage());
            return new EducationTypeResource('Failed to retrieve education type', null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EducationTypeRequest $request, string $education_type)
    {
        try {
            $educationType = EducationType::findOrFail($education_type);
            $educationType->update($request->validated());
            return new EducationTypeResource('Education type updated successfully', $educationType, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new EducationTypeResource('Education type not found', null, 404);
        } catch (\Exception $e) {
            Log::error('Error updating education type: ' . $e->getMessage());
            return new EducationTypeResource('Failed to update education type', null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $education_type)
    {
        try {
            $educationType = EducationType::findOrFail($education_type);
            $educationType->delete();
            return new EducationTypeResource('Education type deleted successfully', null, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new EducationTypeResource('Education type not found', null, 404);
        } catch (\Exception $e) {
            Log::error('Error deleting education type: ' . $e->getMessage());
            return new EducationTypeResource('Failed to delete education type', null, 500);
        }
    }

    /**
     * Display a listing of the trashed resource.
     */
    public function trashed()
    {
        try {
            $educationTypes = EducationType::onlyTrashed()->get();
            return new EducationTypeResource('Trashed education types retrieved successfully', $educationTypes, 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving trashed education types: ' . $e->getMessage());
            return new EducationTypeResource('Failed to retrieve trashed education types', null, 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $education_type)
    {
        try {
            $educationType = EducationType::onlyTrashed()->findOrFail($education_type);
            $educationType->restore();
            return new EducationTypeResource('Education type restored successfully', $educationType, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return new EducationTypeResource('Trashed education type not found', null, 404);
        } catch (\Exception $e) {
            Log::error('Error restoring education type: ' . $e->getMessage());
            return new EducationTypeResource('Failed to restore education type', null, 500);
        }
    }
}
