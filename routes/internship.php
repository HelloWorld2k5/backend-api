<?php

use App\Http\Controllers\Internship\InternshipController;
use Illuminate\Support\Facades\Route;

// Chỉ dành cho Sinh viên đã đăng nhập
Route::middleware(['auth:sanctum', 'role:student'])->prefix('internships')->group(function () {

    // UC 33: Đăng ký đợt thực tập
    Route::post('/register', [InternshipController::class, 'register']);
    // UC 34: Đăng ký doanh nghiệp
    Route::get('/check-company', [InternshipController::class, 'checkCompany']); // Bước 5
    Route::post('/register-company', [InternshipController::class, 'registerCompany']); // Bước 8

    // UC 35:
    // Lấy lịch sử nộp (Bước 4)
    Route::get('/reports/history', [InternshipController::class, 'getReportHistory']);

    // Thực hiện nộp báo cáo (Bước 6)
    Route::post('/reports/submit', [InternshipController::class, 'submitReport']);
});

// Group cho Văn phòng khoa (UC 42)
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk/internships')->group(function () {
    Route::get('/pending', [InternshipController::class, 'getPendingRequests']);
    Route::post('/approve/{id}', [InternshipController::class, 'approveRequest']);
});

// // UC 33 & 34 (Tạm thời bỏ middleware role:student để test nhanh)
// Route::prefix('internships')->group(function () {
//     // UC 33: Đăng ký đợt
//     Route::post('/register-session', [InternshipController::class, 'registerSession']);

//     // UC 34: Đăng ký doanh nghiệp
//     Route::get('/check-company', [InternshipController::class, 'checkCompany']); // Bước 5
//     Route::post('/register-company', [InternshipController::class, 'registerCompany']); // Bước 8
// });
