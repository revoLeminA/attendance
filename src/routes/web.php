<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminStaffAttendanceController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 管理者用ログインルート
Route::post('/login', [AdminLoginController::class, 'store']);

// 共通ユーザ用認証ルート
Route::group(['middleware' => 'auth'] ,function () {
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('auth.attendance.show');
    Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('auth.request.index');
});

// 一般ユーザー用認証ルート
Route::group(['middleware' => 'auth:web'] ,function () {
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('user.attendance.create');
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('user.attendance.index');
});

// 管理者用認証ルート
Route::group(['middleware' => 'auth:admin'] ,function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/admin/attendance/staff/{id}', [AdminStaffAttendanceController::class, 'index'])->name('admin.staff.attendance.index');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminRequestController::class, 'show'])->name('admin.request.show');
});