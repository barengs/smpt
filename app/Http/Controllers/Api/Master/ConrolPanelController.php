<?php

namespace App\Http\Controllers\Api\Master;

use Exception;
use App\Models\ControlPanel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\ImageManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Imagick\Driver;

class ConrolPanelController extends Controller
{
    /**
     * Display the first resource.
     */
    public function index()
    {
        try {
            $controlPanel = ControlPanel::first();

            // If no record exists, create a default one
            if (!$controlPanel) {
                $controlPanel = ControlPanel::create([
                    'app_name' => 'SMP Application',
                    'app_version' => '1.0.0',
                    'app_description' => 'School Management System',
                    'app_theme' => 'system',
                    'app_language' => 'indonesia',
                    'is_maintenance_mode' => 'false'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data control panel berhasil diambil',
                'data' => $controlPanel
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data control panel',
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
            // Check if a record already exists
            if (ControlPanel::count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data control panel sudah ada, gunakan fungsi update untuk mengubah data'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'app_name' => 'required|string|max:255',
                'app_version' => 'nullable|string|max:50',
                'app_description' => 'nullable|string',
                'app_url' => 'nullable|url',
                'app_email' => 'nullable|email',
                'app_phone' => 'nullable|string|max:20',
                'app_address' => 'nullable|string',
                'is_maintenance_mode' => 'required|in:true,false',
                'maintenance_message' => 'nullable|string',
                'app_theme' => 'required|in:light,dark,system',
                'app_language' => 'required|in:indonesia,english,arabic',
                'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'app_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except(['app_logo', 'app_favicon']);

            // Handle logo upload
            if ($request->hasFile('app_logo')) {
                $this->uploadImage($request->file('app_logo'), $validatedData, 'app_logo');
            }

            // Handle favicon upload
            if ($request->hasFile('app_favicon')) {
                $data['app_favicon'] = $this->uploadFavicon($request->file('app_favicon'));
            }

            $controlPanel = ControlPanel::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Data control panel berhasil ditambahkan',
                'data' => $controlPanel
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data control panel',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan data control panel',
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
            $controlPanel = ControlPanel::find($id);

            if (!$controlPanel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data control panel tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data control panel berhasil diambil',
                'data' => $controlPanel
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data control panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ?string $id = null)
    {
        try {
            // If no ID provided, update the first record
            if (!$id) {
                $controlPanel = ControlPanel::first();
                if (!$controlPanel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data control panel tidak ditemukan'
                    ], 404);
                }
            } else {
                $controlPanel = ControlPanel::find($id);
                if (!$controlPanel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data control panel tidak ditemukan'
                    ], 404);
                }
            }

            $validator = Validator::make($request->all(), [
                'app_name' => 'sometimes|required|string|max:255',
                'app_version' => 'nullable|string|max:50',
                'app_description' => 'nullable|string',
                'app_url' => 'nullable|url',
                'app_email' => 'nullable|email',
                'app_phone' => 'nullable|string|max:20',
                'app_address' => 'nullable|string',
                'is_maintenance_mode' => 'sometimes|required|in:true,false',
                'maintenance_message' => 'nullable|string',
                'app_theme' => 'sometimes|required|in:light,dark,system',
                'app_language' => 'sometimes|required|in:indonesia,english,arabic',
                'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'app_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except(['app_logo', 'app_favicon']);

            // Handle logo upload
            if ($request->hasFile('app_logo')) {
                // Delete old logo if exists
                if ($controlPanel->app_logo) {
                    Storage::disk('public')->delete($controlPanel->app_logo);
                }
                $this->uploadImage($request->file('app_logo'), $data, 'app_logo');
            }

            // Handle favicon upload
            if ($request->hasFile('app_favicon')) {
                // Delete old favicon if exists
                if ($controlPanel->app_favicon) {
                    Storage::disk('public')->delete($controlPanel->app_favicon);
                }
                $data['app_favicon'] = $this->uploadFavicon($request->file('app_favicon'));
            }

            $controlPanel->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data control panel berhasil diperbarui',
                'data' => $controlPanel
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data control panel',
                'error' => $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data control panel',
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
            $controlPanel = ControlPanel::find($id);

            if (!$controlPanel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data control panel tidak ditemukan'
                ], 404);
            }

            // Delete logo and favicon files if they exist
            if ($controlPanel->app_logo) {
                Storage::disk('public')->delete($controlPanel->app_logo);
            }
            if ($controlPanel->app_favicon) {
                Storage::disk('public')->delete($controlPanel->app_favicon);
            }

            $controlPanel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data control panel berhasil dihapus'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data control panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update only the app logo.
     */
    public function updateLogo(Request $request)
    {
        try {
            $controlPanel = ControlPanel::first();

            if (!$controlPanel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data control panel tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'app_logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Delete old logo if exists
            if ($controlPanel->app_logo) {
                Storage::disk('public')->delete($controlPanel->app_logo);
            }

            $logoPath = $request->file('app_logo')->store('logos', 'public');
            $controlPanel->update(['app_logo' => $logoPath]);

            return response()->json([
                'success' => true,
                'message' => 'Logo berhasil diperbarui',
                'data' => [
                    'app_logo' => $logoPath
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui logo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update only the app favicon.
     */
    public function updateFavicon(Request $request)
    {
        try {
            $controlPanel = ControlPanel::first();

            if (!$controlPanel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data control panel tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'app_favicon' => 'required|image|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Delete old favicon if exists
            if ($controlPanel->app_favicon) {
                Storage::disk('public')->delete($controlPanel->app_favicon);
            }

            $faviconPath = $request->file('app_favicon')->store('favicons', 'public');
            $controlPanel->update(['app_favicon' => $faviconPath]);

            return response()->json([
                'success' => true,
                'message' => 'Favicon berhasil diperbarui',
                'data' => [
                    'app_favicon' => $faviconPath
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui favicon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function uploadImage($file, &$validatedData, $columnName)
    {
        $image = new ImageManager(new Driver());
        $timestamp = now()->timestamp;
        $fileName = $timestamp . '_' . $file->getClientOriginalName();

        // Large logo
        $largeImage = $image->read($file->getRealPath());
        $largeImage->cover(512, 512);
        Storage::disk('public')->put('uploads/logos/large/' . $fileName, (string) $largeImage->encode());

        // Small logo
        $smallImage = $image->read($file->getRealPath());
        $smallImage->scaleDown(128, 128);
        Storage::disk('public')->put('uploads/logos/small/' . $fileName, (string) $smallImage->encode());

        $validatedData[$columnName] = $fileName;
    }

    private function uploadFavicon($file)
    {
        $fileName = $file->getClientOriginalName();

        // Store favicon in a separate directory without resizing
        $file->storeAs('public/uploads/favicons', $fileName);

        return $fileName;
    }
}
