<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Main\NewsController;
use App\Http\Controllers\Api\Main\RoleController;
use App\Http\Controllers\Api\Main\StaffController;
use App\Http\Controllers\Api\Main\ParentController;
use App\Http\Controllers\Api\Master\CityController;
use App\Http\Controllers\Api\Master\MenuController;
use App\Http\Controllers\Api\Master\RoomController;
use App\Http\Controllers\Api\AuthRegisterController;
use App\Http\Controllers\Api\Main\AccountController;
use App\Http\Controllers\Api\Main\ProductController;
use App\Http\Controllers\Api\Main\StudentController;
use App\Http\Controllers\Api\Master\StudyController;
use App\Http\Controllers\Api\Main\ActivityController;
use App\Http\Controllers\Api\Master\HostelController;
use App\Http\Controllers\Api\Main\DashboardController;
use App\Http\Controllers\Api\Master\ProgramController;
use App\Http\Controllers\Api\Master\VillageController;
use App\Http\Controllers\Api\Main\PermissionController;
use App\Http\Controllers\Api\Master\DistrictController;
use App\Http\Controllers\Api\Master\ProvinceController;
use App\Http\Controllers\Api\Main\TransactionController;
use App\Http\Controllers\Api\Master\EducationController;
use App\Http\Controllers\Api\Main\RegistrationController;
use App\Http\Controllers\Api\Master\ClassGroupController;
use App\Http\Controllers\Api\Master\EmploymentController;
use App\Http\Controllers\Api\Master\LessonHourController;
use App\Http\Controllers\Api\Master\OccupationController;
use App\Http\Controllers\Api\Master\ProfessionController;
use App\Http\Controllers\Api\Master\StaffStudyController;
use App\Http\Controllers\Api\Master\ConrolPanelController;
use App\Http\Controllers\Api\Master\AcademicYearController;
use App\Http\Controllers\Api\Main\TransactionTypeController;
use App\Http\Controllers\Api\Master\EducationTypeController;
use App\Http\Controllers\Api\Master\ChartOfAccountController;
use App\Http\Controllers\Api\Master\IntershipSupervisorController;

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
    Route::get('education-type/trashed', [EducationTypeController::class, 'trashed']);
    Route::apiResource('education-type', EducationTypeController::class);
    Route::post('education-type/{education_type}/restore', [EducationTypeController::class, 'restore']);
    Route::get('academic-year/trashed', [AcademicYearController::class, 'trashed']);
    Route::post('academic-year/{id}/restore', [AcademicYearController::class, 'restore']);
    Route::apiResource('academic-year', AcademicYearController::class);
    Route::get('occupation/trashed', [OccupationController::class, 'trashed']);
    Route::post('occupation/{id}/restore', [OccupationController::class, 'restore']);
    Route::apiResource('occupation', OccupationController::class);
    Route::get('employment/trashed', [EmploymentController::class, 'trashed']);
    Route::post('employment/{id}/restore', [EmploymentController::class, 'restore']);
    Route::apiResource('employment', EmploymentController::class);
    // Chart Of Account
    Route::get('chart-of-account/header-accounts', [ChartOfAccountController::class, 'headerAccounts'])->name('chart-of-account.header-accounts');
    Route::get('chart-of-account/detail-accounts', [ChartOfAccountController::class, 'detailAccounts'])->name('chart-of-account.detail-accounts');
    Route::apiResource('chart-of-account', ChartOfAccountController::class);
    Route::get('lesson-hour/trashed', [LessonHourController::class, 'trashed']);
    Route::post('lesson-hour/{id}/restore', [LessonHourController::class, 'restore']);
    Route::apiResource('lesson-hour', LessonHourController::class);
    Route::get('room/trashed', [RoomController::class, 'trashed']);
    Route::post('room/{id}/restore', [RoomController::class, 'restore']);
    Route::apiResource('room', RoomController::class);
    Route::get('supervisor/trashed', [IntershipSupervisorController::class, 'trashed']);
    Route::post('supervisor/{id}/restore', [IntershipSupervisorController::class, 'restore']);
    Route::apiResource('supervisor', IntershipSupervisorController::class);
    Route::apiResource('hostel', HostelController::class);

    // Control Panel routes
    Route::get('control-panel', [ConrolPanelController::class, 'index']);
    Route::post('control-panel', [ConrolPanelController::class, 'store']);
    Route::get('control-panel/{id}', [ConrolPanelController::class, 'show']);
    Route::put('control-panel/{id?}', [ConrolPanelController::class, 'update']);
    Route::delete('control-panel/{id}', [ConrolPanelController::class, 'destroy']);
    Route::post('control-panel/logo', [ConrolPanelController::class, 'updateLogo']);
    Route::post('control-panel/favicon', [ConrolPanelController::class, 'updateFavicon']);

    // Menu routes
    Route::apiResource('menu', MenuController::class);

    // Product routes
    Route::apiResource('product', ProductController::class);
});

// Main routes
Route::group(['prefix' => 'main'], function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/student-statistics-by-period', [DashboardController::class, 'studentStatisticsByPeriod'])->name('dashboard.student-statistics-by-period');

    Route::apiResource('control-panel', ConrolPanelController::class);
    // Registration routes
    Route::apiResource('registration', RegistrationController::class);
    Route::get('registration/curent-year', [RegistrationController::class, 'getByCurrentYear'])->name('registration.current-year');
    Route::post('registration/transaction', [TransactionController::class, 'createRegistrationTransaction'])->name('transaction.transaction');

    // Transaction routes
    Route::apiResource('transaction', TransactionController::class);
    Route::apiResource('transaction-type', TransactionTypeController::class);

    // Account routes
    Route::apiResource('account', AccountController::class);

    // News routes
    Route::apiResource('news', NewsController::class);
    Route::apiResource('activity', ActivityController::class);

    // Role routes
    Route::apiResource('role', RoleController::class);
    Route::post('role/{id}/assign-permissions', [RoleController::class, 'assignPermissions']);
    Route::post('role/{id}/remove-permissions', [RoleController::class, 'removePermissions']);

    // Permission routes
    Route::apiResource('permission', PermissionController::class);
    Route::post('permission/{id}/assign-roles', [PermissionController::class, 'assignRoles']);
    Route::post('permission/{id}/remove-roles', [PermissionController::class, 'removeRoles']);

    // Staff routes
    Route::apiResource('staff', StaffController::class);
    Route::post('/{id}/restore', [StaffController::class, 'restore']);
    Route::delete('/{id}/force-delete', [StaffController::class, 'forceDelete']);
    Route::get('/{id}/user', [StaffController::class, 'getByUserId']);
    Route::put('/{id}/status', [StaffController::class, 'updateStatus']);
    Route::get('/trashed', [StaffController::class, 'trashed']);
    Route::get('/statistics', [StaffController::class, 'statistics']);
    Route::post('/bulk-delete', [StaffController::class, 'bulkDelete']);
    Route::post('/bulk-restore', [StaffController::class, 'bulkRestore']);
    Route::post('/bulk-force-delete', [StaffController::class, 'bulkForceDelete']);

    Route::apiResource('staff-study', StaffStudyController::class);

    // Student
    Route::apiResource('student', StudentController::class);
    Route::apiResource('parent', ParentController::class);
    Route::get('parent/nik/{nik}/cek', [ParentController::class, 'getByNik'])
    ->name('parent.getByNik');
});
