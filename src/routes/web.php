<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthenticateController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminStaffAttendanceController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\MailVerifyController;

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

// メール認証
Route::group(['middleware' => ['auth']], function () {
    Route::get('/email/verify', [MailVerifyController::class, 'notice'])->name('verification.notice'); // メール認証誘導画面
    Route::get('/email/verify/{id}/{hash}', [MailVerifyController::class, 'verify'])->middleware(['signed'])->name('verification.verify'); // メール認証処理
    Route::post('/email/verification-notification', [MailVerifyController::class, 'send'])->middleware(['throttle:6,1'])->name('verification.send'); // メール確認の再送信
});

// 管理者用ログインルート
Route::group(['prefix' => '/admin'] ,function () {
    Route::get('/login', [AdminAuthenticateController::class, 'create'])->name('auth.admin-login'); // ログイン画面（管理者）
    Route::post('/login', [AdminAuthenticateController::class, 'store']);
    Route::post('/logout', [AdminAuthenticateController::class, 'destroy']);
});

Route::group(['middleware' => ['verified']], function () {
    // 一般ユーザー用認証ルート
    Route::group(['middleware' => ['auth:web']] ,function () {
        Route::get('/attendance', [AttendanceController::class, 'create'])->name('user.attendance.create'); // 勤怠登録画面（一般ユーザ）
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('user.attendance.store');
        Route::patch('/attendance', [AttendanceController::class, 'update'])->name('user.attendance.update');
        Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('user.attendance.index'); // 勤怠一覧画面（一般ユーザ）
    });

    // 管理者用認証ルート
    Route::group(['middleware' => ['auth:admin']] ,function () {
        Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index'); // 勤怠一覧画面（管理者）
        Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.index'); // スタッフ一覧画面（管理者）
        Route::get('/admin/attendance/staff/{id}', [AdminStaffAttendanceController::class, 'index'])->name('admin.staff.attendance.index'); // スタッフ別勤怠一覧画面（管理者）
        Route::post('/admin/attendance/staff/{id}', [AdminStaffAttendanceController::class, 'export'])->name('admin.staff.attendance.csv');
        Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminRequestController::class, 'show'])->name('admin.request.show'); // 修正申請承認画面（管理者）
        Route::patch('/stamp_correction_request/approve/{attendance_correct_request}', [AdminRequestController::class, 'update'])->name('admin.request.update');
    });

    // 共通ユーザ用認証ルート
    Route::group(['middleware' => ['auth:admin,web']] ,function () {
        Route::get('/attendance/{id}', [AttendanceDetailController::class, 'show'])->name('auth.attendance.show'); // 勤怠詳細画面（一般ユーザ、管理者）
        Route::patch('/attendance/{id}', [AttendanceDetailController::class, 'update'])->name('auth.attendance.update');
        Route::get('/stamp_correction_request/list', [RequestController::class, 'index'])->name('auth.request.index'); // 申請一覧画面（一般ユーザ、管理者）
    });
});
