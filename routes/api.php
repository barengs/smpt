<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthRegisterController;
use App\Http\Controllers\Api\Master\AcademicYearController;
use App\Http\Controllers\Api\Master\ClassGroupController;
use App\Http\Controllers\Api\Master\EducationClassController;
use App\Http\Controllers\Api\Master\EducationController;
use App\Http\Controllers\Api\Master\EducationTypeController;
use App\Http\Controllers\Api\Master\LessonHourController;
use App\Http\Controllers\Api\Master\ProvinceController;
use App\Http\Controllers\Api\Master\CityController;
use App\Http\Controllers\Api\Master\DistrictController;
use App\Http\Controllers\Api\Master\VillageController;
use App\Http\Controllers\Api\Master\ProfessionController;
use App\Http\Controllers\Api\Master\ProgramController;
use App\Http\Controllers\Api\Master\StudyController;
use App\Http\Controllers\Api\Master\ChartOfAccountController;
use App\Http\Controllers\Api\Master\RoomController;
use App\Http\Controllers\Api\Master\IntershipSupervisorController;
use App\Http\Controllers\Api\Master\HostelController;
use App\Http\Controllers\Api\Master\ClassroomController;
use App\Http\Controllers\Api\Master\ConrolPanelController;
use App\Http\Controllers\Api\Master\MenuController;
use App\Http\Controllers\Api\Master\OrganizationController;
use App\Http\Controllers\Api\Master\PositionController;
use App\Http\Controllers\Api\Master\PositionAssignmentController;
use App\Http\Controllers\Api\Main\ProductController;
use App\Http\Controllers\Api\Master\StaffStudyController;
use App\Http\Controllers\Api\Master\OccupationController;
use App\Http\Controllers\Api\Master\EmploymentController;
use App\Http\Controllers\Api\Main\DashboardController;
use App\Http\Controllers\Api\Main\RegistrationController;
use App\Http\Controllers\Api\Main\TransactionController;
use App\Http\Controllers\Api\Main\AccountController;
use App\Http\Controllers\Api\Main\NewsController;
use App\Http\Controllers\Api\Main\ActivityController;
use App\Http\Controllers\Api\Main\RoleController;
use App\Http\Controllers\Api\Main\PermissionController;
use App\Http\Controllers\Api\Main\StaffController;
use App\Http\Controllers\Api\Main\StudentController;
use App\Http\Controllers\Api\Main\ParentController;
use App\Http\Controllers\Api\Main\ClassScheduleController;
use App\Http\Controllers\Api\Main\StudentClassController;
use App\Http\Controllers\Api\Main\EducationalInstitutionController;
use App\Http\Controllers\Api\Main\TransactionTypeController;
use App\Http\Controllers\Api\Main\InternshipController;
use App\Http\Controllers\Api\Main\PresenceController;
use App\Http\Controllers\Api\Master\ViolationCategoryController;
use App\Http\Controllers\Api\Master\ViolationController;
use App\Http\Controllers\Api\Master\SanctionController;
use App\Http\Controllers\Api\Main\StudentViolationController;
use App\Http\Controllers\Api\Master\LeaveTypeController;
use App\Http\Controllers\Api\Main\StudentLeaveController;
use App\Http\Controllers\Api\Main\RoleMenuController;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Education;
use App\Models\EducationClass;
use App\Models\Program;
use App\Models\Study;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('profile', [AuthController::class, 'me']);
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
    Route::get('study/export', [StudyController::class, 'export']);
    Route::get('study/backup', [StudyController::class, 'backup']);
    Route::apiResource('study', StudyController::class);
    Route::get('class-group/trashed', [ClassGroupController::class, 'trashed']);
    Route::post('class-group/{id}/restore', [ClassGroupController::class, 'restore']);
    Route::post('class-group/import', [ClassGroupController::class, 'import']);
    Route::get('class-group/import/template', [ClassGroupController::class, 'downloadTemplate']);
    Route::get('class-group/advisors', [ClassGroupController::class, 'getAdvisors']);
    Route::get('class-group/details', [ClassGroupController::class, 'getClassGroupsWithDetails']);
    Route::post('class-group/{id}/assign-advisor', [ClassGroupController::class, 'assignAdvisor']);
    Route::post('class-group/{id}/remove-advisor', [ClassGroupController::class, 'removeAdvisor']);
    Route::get('class-group/export', [ClassGroupController::class, 'export']);
    Route::get('class-group/backup', [ClassGroupController::class, 'backup']);
    Route::apiResource('class-group', ClassGroupController::class);
    Route::get('education/trashed', [EducationController::class, 'trashed']);
    Route::post('education/{id}/restore', [EducationController::class, 'restore']);
    Route::get('education/export', [EducationController::class, 'export']);
    Route::get('education/backup', [EducationController::class, 'backup']);
    Route::apiResource('education', EducationController::class);
    Route::get('education-type/trashed', [EducationTypeController::class, 'trashed']);
    Route::apiResource('education-type', EducationTypeController::class);
    Route::post('education-type/{education_type}/restore', [EducationTypeController::class, 'restore']);
    Route::get('academic-year/trashed', [AcademicYearController::class, 'trashed']);
    Route::post('academic-year/{id}/restore', [AcademicYearController::class, 'restore']);
    Route::get('academic-year/active', [AcademicYearController::class, 'showActiveAcademic'])->name('academic-year.active');
    Route::get('academic-year/export', [AcademicYearController::class, 'export']);
    Route::get('academic-year/backup', [AcademicYearController::class, 'backup']);
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
    Route::get('room/export', [RoomController::class, 'export']);
    Route::get('room/backup', [RoomController::class, 'backup']);
    Route::apiResource('room', RoomController::class);
    Route::get('supervisor/trashed', [IntershipSupervisorController::class, 'trashed']);
    Route::post('supervisor/{id}/restore', [IntershipSupervisorController::class, 'restore']);
    Route::apiResource('supervisor', IntershipSupervisorController::class);
    Route::get('hostel/staff/heads', [HostelController::class, 'getHeadStaff']);
    Route::post('hostel/{id}/assign-head', [HostelController::class, 'assignHead']);
    Route::get('hostel/{id}/head/current', [HostelController::class, 'currentHead']);
    Route::get('hostel/{id}/head/history', [HostelController::class, 'headHistory']);
    Route::get('hostel/export', [HostelController::class, 'export']);
    Route::get('hostel/backup', [HostelController::class, 'backup']);
    Route::apiResource('hostel', HostelController::class);
    Route::get('classroom/export', [ClassroomController::class, 'export']);
    Route::get('classroom/backup', [ClassroomController::class, 'backup']);
    Route::apiResource('classroom', ClassroomController::class);
    Route::apiResource('class-group', ClassGroupController::class);
    Route::get('education-class/export', [EducationClassController::class, 'export']);
    Route::get('education-class/backup', [EducationClassController::class, 'backup']);
    Route::apiResource('education-class', EducationClassController::class);
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
    Route::post('menu/{id}/assign-permissions', [MenuController::class, 'assignMenuPermission']);
    Route::get('menu/{id}/permissions', [MenuController::class, 'getMenuPermissions']);
    Route::post('menu/{id}/remove-permissions', [MenuController::class, 'removeMenuPermission']);

    // Product routes
    Route::apiResource('product', ProductController::class);

    Route::apiResource('staff-study', StaffStudyController::class);
    Route::get('staff-study/teachers/all', [StaffStudyController::class, 'getAllTeachers'])->name('staff-study.teachers.all');

    // Organization routes
    Route::apiResource('organization', OrganizationController::class);
    Route::get('organization/root', [OrganizationController::class, 'getRootOrganizations']);
    Route::get('organization/hierarchy', [OrganizationController::class, 'getHierarchy']);

    // Position routes
    Route::apiResource('position', PositionController::class);
    Route::get('position/organization/{organizationId}', [PositionController::class, 'getByOrganization']);

    // Position Assignment routes
    Route::apiResource('position-assignment', PositionAssignmentController::class);
    Route::get('position-assignment/current', [PositionAssignmentController::class, 'getCurrent']);
    Route::get('position-assignment/staff/{staffId}', [PositionAssignmentController::class, 'getByStaff']);
    Route::get('position-assignment/position/{positionId}', [PositionAssignmentController::class, 'getByPosition']);

    // Tata Tertib - Violation Categories
    Route::apiResource('violation-category', ViolationCategoryController::class);

    // Tata Tertib - Violations
    Route::apiResource('violation', ViolationController::class);

    // Tata Tertib - Sanctions
    Route::apiResource('sanction', SanctionController::class);

    // Leave Types (Jenis Izin)
    Route::apiResource('leave-type', LeaveTypeController::class);
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
    Route::post('registration/transaction', [RegistrationController::class, 'createRequestTransaction'])->name('registration.transaction');
    Route::get('registration/student/{nik}/check', [RegistrationController::class, 'checkTtl'])->name('registration.student.check');

    // Transaction routes
    Route::put('transaction/{id}/activate', [
        TransactionController::class,
        'activateTransaction'
    ])->name('transaction.activate');
    Route::apiResource('transaction', TransactionController::class);
    Route::apiResource('transaction-type', TransactionTypeController::class);

    // Account routes
    Route::apiResource('account', AccountController::class);
    Route::get('transaction/account/{accountNumber}/last-7-days', [
        TransactionController::class,
        'getLast7DaysTransactions'
    ])->name('transaction.last-7-days');

    // News routes
    Route::apiResource('news', NewsController::class);
    Route::apiResource('activity', ActivityController::class);

    // Role routes
    Route::apiResource('role', RoleController::class);
    Route::post('role/{id}/assign-permissions', [RoleController::class, 'assignPermissions']);
    Route::post('role/{id}/remove-permissions', [RoleController::class, 'removePermissions']);

    // Role-Menu routes
    Route::apiResource('role-menu', RoleMenuController::class);
    Route::get('role/{roleId}/menus', [RoleMenuController::class, 'getRoleMenus']);
    Route::get('menu/{menuId}/roles', [RoleMenuController::class, 'getMenuRoles']);
    Route::post('role/{roleId}/assign-menus', [RoleMenuController::class, 'assignMenuToRole']);
    Route::post('role/{roleId}/remove-menus', [RoleMenuController::class, 'removeMenuFromRole']);
    Route::get('user/menus', [RoleMenuController::class, 'getUserMenus']);

    // Permission routes
    Route::apiResource('permission', PermissionController::class);
    Route::post('permission/{id}/assign-roles', [PermissionController::class, 'assignRoles']);
    Route::post('permission/{id}/remove-roles', [PermissionController::class, 'removeRoles']);

    // Staff routes
    Route::post('staff/import', [StaffController::class, 'import']);
    Route::get('staff/export', [StaffController::class, 'export']);
    Route::get('staff/backup', [StaffController::class, 'backup']);
    Route::get('staff/import/template', [StaffController::class, 'downloadTemplate']);
    Route::apiResource('staff', StaffController::class);
    Route::get('staff/teachers/roles', [StaffController::class, 'getStaffByRoles'])->name('staff.by-roles');
    Route::get('staff/teachers/roles/{id}', [StaffController::class, 'getStaffByRolesById'])->name('staff.by-roles.id');
    Route::get('staff/by-category/{category}', [StaffController::class, 'getStaffByCategory'])->name('staff.by-category');
    Route::get('staff/role-categories', [StaffController::class, 'getRoleCategories'])->name('staff.role-categories');
    Route::post('roles/{id}/sync-access', [RoleMenuController::class, 'syncRoleAccess'])->name('roles.sync-access');
    Route::post('staff/check-nik', [StaffController::class, 'checkNik'])->name('staff.check-nik');

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
    Route::post('student/import', [StudentController::class, 'import']);
    Route::get('student/export', [StudentController::class, 'export']);
    Route::get('student/backup', [StudentController::class, 'backup']);
    Route::get('student/import/template', [StudentController::class, 'downloadTemplate']);
    Route::post('student/{id}/room/assign', [StudentController::class, 'assignRoom']);
    Route::get('student/{id}/room/history', [StudentController::class, 'roomHistory']);
    Route::apiResource('student', StudentController::class);
    Route::post('student/{id}/update-photo', [StudentController::class, 'updatePhoto'])->name('student.update-photo');

    // Parent
    Route::post('parent/import', [ParentController::class, 'import']);
    Route::get('parent/import/template', [ParentController::class, 'downloadTemplate']);
    Route::apiResource('parent', ParentController::class);
    Route::get('parent/nik/{nik}/cek', [ParentController::class, 'getByNik'])
        ->name('parent.getByNik');

    // Class Schedule
    // Class Schedule
    Route::get('class-schedule/export', [ClassScheduleController::class, 'export']);
    Route::get('class-schedule/backup', [ClassScheduleController::class, 'backup']);
    Route::apiResource('class-schedule', ClassScheduleController::class);

    // Student Class
    Route::get('student-class/class-group/{classGroupId}/students', [StudentClassController::class, 'getStudentsMappedToClassGroups']);
    Route::post('student-class/bulk-assign', [StudentClassController::class, 'bulkAssign']);
    Route::post('student-class/{id}/approve', [StudentClassController::class, 'approve']);
    Route::post('student-class/{id}/reject', [StudentClassController::class, 'reject']);
    Route::apiResource('student-class', StudentClassController::class);

    // Educational Institution
    Route::get('educational-institution/export', [EducationalInstitutionController::class, 'export']);
    Route::get('educational-institution/backup', [EducationalInstitutionController::class, 'backup']);
    Route::apiResource('educational-institution', EducationalInstitutionController::class);

    // Internship
    Route::apiResource('internship', InternshipController::class);

    // Presence
    Route::apiResource('presence', PresenceController::class);
    Route::get('presence/statistics', [PresenceController::class, 'statistics'])->name('presence.statistics');

    // Tata Tertib    // Student Violations
    Route::get('student-violation/download-report', [StudentViolationController::class, 'downloadReport']);
    Route::get('student-violation/statistics', [StudentViolationController::class, 'statistics']);
    Route::get('student-violation/student/{studentId}', [StudentViolationController::class, 'reportByStudent']);
    Route::post('student-violation/{id}/sanction', [StudentViolationController::class, 'assignSanction']);
    Route::apiResource('student-violation', StudentViolationController::class);

    // Student Leaves (Perizinan Santri)
    Route::get('student-leave/download-report', [StudentLeaveController::class, 'downloadReport']);
    Route::get('student-leave/statistics', [StudentLeaveController::class, 'statistics']);
    Route::get('student-leave/student/{studentId}/report', [StudentLeaveController::class, 'reportByStudent']);
    Route::get('student-leave/{id}/approval-history', [StudentLeaveController::class, 'approvalHistory']);
    Route::get('student-leave/{id}/activity-history', [StudentLeaveController::class, 'activityHistory']);
    Route::post('student-leave/{id}/approve', [StudentLeaveController::class, 'approve']);
    Route::post('student-leave/{id}/reject', [StudentLeaveController::class, 'reject']);
    Route::post('student-leave/{id}/approve-by-role', [StudentLeaveController::class, 'approveByRole']);
    Route::post('student-leave/{id}/reject-by-role', [StudentLeaveController::class, 'rejectByRole']);
    Route::post('student-leave/{id}/submit-report', [StudentLeaveController::class, 'submitReport']);
    Route::post('student-leave/{id}/verify-report', [StudentLeaveController::class, 'verifyReport']);
    Route::post('student-leave/{id}/assign-penalty', [StudentLeaveController::class, 'assignPenalty']);
    Route::post('student-leave/check-overdue', [StudentLeaveController::class, 'checkOverdueLeaves']);
    Route::apiResource('student-leave', StudentLeaveController::class);
});
