<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark_all_read');

Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::prefix('admin')->name('admin.')->group(function () {
	Route::get('/users', [AdminController::class, 'users'])->name('users');
	Route::get('/coordinators', [AdminController::class, 'coordinators'])->name('coordinators');
	Route::post('/coordinators', [AdminController::class, 'createCoordinator'])->name('coordinators.store');
	Route::get('/partners', [AdminController::class, 'partners'])->name('partners');
	Route::post('/partners', [AdminController::class, 'createCompany'])->name('partners.store');
	Route::post('/partners/resend-credentials', [AdminController::class, 'resendCompanyCredentials'])->name('partners.resend_credentials');
	Route::post('/users/reset-credentials', [AdminController::class, 'resetUserCredentials'])->name('users.reset_credentials');
	Route::post('/users/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
	Route::get('/programs', [AdminController::class, 'programs'])->name('programs');
	Route::post('/programs', [AdminController::class, 'saveProgram'])->name('programs.save');
	Route::post('/programs/delete', [AdminController::class, 'deleteProgram'])->name('programs.delete');
	Route::get('/email-logs', [AdminController::class, 'emailLogs'])->name('email_logs');
	Route::get('/evaluations', [AdminController::class, 'evaluations'])->name('evaluations');
});

Route::get('/coordinator', [CoordinatorController::class, 'dashboard'])->name('coordinator.dashboard');
Route::prefix('coordinator')->name('coordinator.')->group(function () {
	Route::get('/manage', [CoordinatorController::class, 'manage'])->name('manage');
	Route::post('/students', [CoordinatorController::class, 'createStudent'])->name('students.store');
	Route::post('/enrollments', [CoordinatorController::class, 'enrollStudent'])->name('enrollments.store');
	Route::get('/students', [CoordinatorController::class, 'students'])->name('students');
	Route::post('/requirements/review', [CoordinatorController::class, 'reviewRequirement'])->name('requirements.review');
	Route::post('/students/reset-password', [CoordinatorController::class, 'resetStudentPassword'])->name('students.reset_password');
	Route::post('/deployments/forward', [CoordinatorController::class, 'forwardDeployment'])->name('deployments.forward');
	Route::get('/evaluations', [CoordinatorController::class, 'evaluations'])->name('evaluations');
});

Route::get('/student', [StudentController::class, 'dashboard'])->name('student.dashboard');
Route::prefix('student')->name('student.')->group(function () {
	Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
	Route::post('/profile', [StudentController::class, 'saveProfile'])->name('profile.save');
	Route::get('/change-password', [StudentController::class, 'password'])->name('password.edit');
	Route::post('/change-password', [StudentController::class, 'changePassword'])->name('password.update');
	Route::post('/requirements/upload', [StudentController::class, 'uploadRequirement'])->name('requirements.upload');
	Route::post('/requirements/submit', [StudentController::class, 'submitRequirements'])->name('requirements.submit');
	Route::post('/dtr', [StudentController::class, 'addDtr'])->name('dtr.store');
	Route::post('/weekly-reports', [StudentController::class, 'addWeekly'])->name('weekly_reports.store');
	Route::post('/reports/upload', [StudentController::class, 'uploadReport'])->name('reports.upload');
});

Route::get('/partner', [PartnerController::class, 'dashboard'])->name('partner.dashboard');
Route::prefix('partner')->name('partner.')->group(function () {
	Route::post('/deployments/accept', [PartnerController::class, 'acceptDeployment'])->name('deployments.accept');
	Route::post('/orientation/email', [PartnerController::class, 'sendOrientationEmail'])->name('orientation.email');
	Route::post('/orientation/schedule', [PartnerController::class, 'scheduleOrientation'])->name('orientation.schedule');
	Route::post('/orientation/complete', [PartnerController::class, 'completeOrientation'])->name('orientation.complete');
	Route::post('/evaluations', [PartnerController::class, 'submitEvaluation'])->name('evaluations.store');
});

Route::get('/index.php', [DashboardController::class, 'redirectOldRoute'])->name('compat.index');
Route::get('/auth.php', fn () => redirect()->route('login'))->name('compat.auth');
Route::get('/logout.php', [AuthController::class, 'logout']);

Route::prefix('practicum-system')->group(function () {
	Route::get('/', [DashboardController::class, 'index']);
	Route::get('/dashboard', [DashboardController::class, 'index']);
	Route::get('/login', [AuthController::class, 'showLogin']);
	Route::post('/login', [AuthController::class, 'login']);
	Route::post('/logout', [AuthController::class, 'logout']);
	Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);

	Route::get('/admin', [AdminController::class, 'dashboard']);
	Route::get('/admin/users', [AdminController::class, 'users']);
	Route::get('/admin/coordinators', [AdminController::class, 'coordinators']);
	Route::post('/admin/coordinators', [AdminController::class, 'createCoordinator']);
	Route::get('/admin/partners', [AdminController::class, 'partners']);
	Route::post('/admin/partners', [AdminController::class, 'createCompany']);
	Route::post('/admin/partners/resend-credentials', [AdminController::class, 'resendCompanyCredentials']);
	Route::post('/admin/users/reset-credentials', [AdminController::class, 'resetUserCredentials']);
	Route::post('/admin/users/toggle', [AdminController::class, 'toggleUser']);
	Route::get('/admin/programs', [AdminController::class, 'programs']);
	Route::post('/admin/programs', [AdminController::class, 'saveProgram']);
	Route::post('/admin/programs/delete', [AdminController::class, 'deleteProgram']);
	Route::get('/admin/email-logs', [AdminController::class, 'emailLogs']);
	Route::get('/admin/evaluations', [AdminController::class, 'evaluations']);

	Route::get('/coordinator', [CoordinatorController::class, 'dashboard']);
	Route::get('/coordinator/manage', [CoordinatorController::class, 'manage']);
	Route::post('/coordinator/students', [CoordinatorController::class, 'createStudent']);
	Route::post('/coordinator/enrollments', [CoordinatorController::class, 'enrollStudent']);
	Route::get('/coordinator/students', [CoordinatorController::class, 'students']);
	Route::post('/coordinator/requirements/review', [CoordinatorController::class, 'reviewRequirement']);
	Route::post('/coordinator/students/reset-password', [CoordinatorController::class, 'resetStudentPassword']);
	Route::post('/coordinator/deployments/forward', [CoordinatorController::class, 'forwardDeployment']);
	Route::get('/coordinator/evaluations', [CoordinatorController::class, 'evaluations']);

	Route::get('/student', [StudentController::class, 'dashboard']);
	Route::get('/student/profile', [StudentController::class, 'profile']);
	Route::post('/student/profile', [StudentController::class, 'saveProfile']);
	Route::get('/student/change-password', [StudentController::class, 'password']);
	Route::post('/student/change-password', [StudentController::class, 'changePassword']);
	Route::post('/student/requirements/upload', [StudentController::class, 'uploadRequirement']);
	Route::post('/student/requirements/submit', [StudentController::class, 'submitRequirements']);
	Route::post('/student/dtr', [StudentController::class, 'addDtr']);
	Route::post('/student/weekly-reports', [StudentController::class, 'addWeekly']);
	Route::post('/student/reports/upload', [StudentController::class, 'uploadReport']);

	Route::get('/partner', [PartnerController::class, 'dashboard']);
	Route::post('/partner/deployments/accept', [PartnerController::class, 'acceptDeployment']);
	Route::post('/partner/orientation/email', [PartnerController::class, 'sendOrientationEmail']);
	Route::post('/partner/orientation/schedule', [PartnerController::class, 'scheduleOrientation']);
	Route::post('/partner/orientation/complete', [PartnerController::class, 'completeOrientation']);
	Route::post('/partner/evaluations', [PartnerController::class, 'submitEvaluation']);

	Route::get('/index.php', [DashboardController::class, 'redirectOldRoute']);
	Route::get('/auth.php', fn () => redirect()->route('login'));
	Route::get('/logout.php', [AuthController::class, 'logout']);
});
