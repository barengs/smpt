<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Main\StaffController;
use App\Http\Controllers\Api\Master\CityController;
use App\Http\Controllers\Api\AuthRegisterController;
use App\Http\Controllers\Api\Master\VillageController;
use App\Http\Controllers\Api\Master\DistrictController;
use App\Http\Controllers\Api\Master\ProvinceController;
use App\Http\Controllers\Api\Master\ProfessionController;
use App\Http\Controllers\Api\Master\ProgramController;
use App\Http\Controllers\Api\Master\StudyController;
use App\Http\Controllers\Api\Master\ClassGroupController;
use App\Http\Controllers\Api\Master\EducationController;
use App\Http\Controllers\Api\Master\OccupationController;
use App\Http\Controllers\Api\Master\EmploymentController;
use App\Http\Controllers\Api\Master\ChartOfAccountController;

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('register', [AuthRegisterController::class, 'register']);

});

Route::group([

    'middleware' => 'api',
    'prefix' => 'staff'

], function ($router) {

    Route::get('/', [StaffController::class, 'index']);
    Route::post('/', [StaffController::class, 'store']);
    Route::get('/{id}', [StaffController::class, 'show']);
    Route::put('/{id}', [StaffController::class, 'update']);
    Route::delete('/{id}', [StaffController::class, 'destroy']);
    Route::post('/{id}/restore', [StaffController::class, 'restore']);
    Route::delete('/{id}/force-delete', [StaffController::class, 'forceDelete']);
    Route::get('/{id}/user', [StaffController::class, 'getByUserId']);
    Route::put('/{id}/status', [StaffController::class, 'updateStatus']);
    Route::get('/trashed', [StaffController::class, 'trashed']);
    Route::get('/statistics', [StaffController::class, 'statistics']);
    Route::post('/bulk-delete', [StaffController::class, 'bulkDelete']);
    Route::post('/bulk-restore', [StaffController::class, 'bulkRestore']);
    Route::post('/bulk-force-delete', [StaffController::class, 'bulkForceDelete']);

});

Route::group(['prefix' => 'master'], function () {
    Route::apiResource('province', ProvinceController::class);
    Route::apiResource('city', CityController::class);
    Route::apiResource('district', DistrictController::class);
    Route::apiResource('village', VillageController::class);
    Route::get('village/{id}/district', [VillageController::class, 'showByDistrict']);
    Route::get('village/{nik}/nik', [VillageController::class, 'showByNik']);
    Route::get('profession/trashed', [ProfessionController::class, 'trashed'])->name('profession.trashed');
    Route::post('profession/{id}/restore', [ProfessionController::class, 'restore']);
    Route::apiResource('profession', ProfessionController::class);
    Route::get('program/trashed', [ProgramController::class, 'trashed']);
    Route::post('program/{id}/restore', [ProgramController::class, 'restore']);
    Route::apiResource('program', ProgramController::class);
    Route::get('study/trashed', [StudyController::class, 'trashed']);
    Route::post('study/{id}/restore', [StudyController::class, 'restore']);
    Route::apiResource('study', StudyController::class);
    Route::get('class-group/trashed', [ClassGroupController::class, 'trashed']);
    Route::post('class-group/{id}/restore', [ClassGroupController::class, 'restore']);
    Route::apiResource('class-group', ClassGroupController::class);
    Route::get('education/trashed', [EducationController::class, 'trashed']);
    Route::post('education/{id}/restore', [EducationController::class, 'restore']);
    Route::apiResource('education', EducationController::class);
    Route::get('occupation/trashed', [OccupationController::class, 'trashed']);
    Route::post('occupation/{id}/restore', [OccupationController::class, 'restore']);
    Route::apiResource('occupation', OccupationController::class);
    Route::get('employment/trashed', [EmploymentController::class, 'trashed']);
    Route::post('employment/{id}/restore', [EmploymentController::class, 'restore']);
    Route::apiResource('employment', EmploymentController::class);
    Route::get('chart-of-account/header-accounts', [ChartOfAccountController::class, 'headerAccounts'])->name('chart-of-account.header-accounts');
    Route::get('chart-of-account/detail-accounts', [ChartOfAccountController::class, 'detailAccounts'])->name('chart-of-account.detail-accounts');
    Route::apiResource('chart-of-account', ChartOfAccountController::class);
});
