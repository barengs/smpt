<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Education;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    /**
     * Read-only list of education levels for parent profile & registration.
     * Full educational institution/curriculum management is handled by Tarbiyah service.
     */
    public function index(Request $request)
    {
        $educations = Education::orderBy('id')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $educations,
        ]);
    }
}

