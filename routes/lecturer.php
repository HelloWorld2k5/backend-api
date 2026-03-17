<?php

use App\Http\Controllers\Lecturer\LecturerController;
use App\Http\Controllers\Internship\InternshipController;
use Illuminate\Support\Facades\Route;

// Các route cho VPK (Duyệt nghỉ phép - UC 48)
Route::middleware(['auth:sanctum', 'role:vpk'])->prefix('vpk')->group(function () {
    Route::get('/lecturers', [LecturerController::class, 'index']);           // Bước 2: Danh sách
    Route::get('/lecturers/{id}', [LecturerController::class, 'show']);       // Bước 4: Chi tiết
    Route::post('/lecturers/{id}/approve', [LecturerController::class, 'approveLeave']); // Bước 5: Duyệt
});

// Các route chung hoặc cho vai trò khác (UC 47)
Route::middleware(['auth:sanctum', 'role:vpk,admin,student'])->group(function () {
    Route::get('/lecturers/search', [LecturerController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {
    Route::get('/pending-reports', [InternshipController::class, 'getReportsToReview']); // UC 40
    Route::post('/reports/{id}/review', [InternshipController::class, 'reviewReport']);   // UC 40
});

//UC 36
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {
    // Tìm kiếm trong phạm vi SV hướng dẫn
    Route::get('/search', [InternshipController::class, 'search']);
});
//UC41
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {

    // Bước 3: Danh sách sinh viên cần chấm điểm
    Route::get('/grading-list', [InternshipController::class, 'getStudentsForGrading']);

    // Bước 7: Thực hiện gửi điểm
    Route::post('/{id}/grade', [InternshipController::class, 'submitGrade']);
});
//UC 39
Route::middleware(['auth:sanctum', 'role:lecturer'])->prefix('lecturer/internships')->group(function () {
    // UC 39.1
    Route::get('/pending-cancels', [InternshipController::class, 'getPendingCancelLecturer']);
    Route::post('/review-cancel/{id}', [InternshipController::class, 'reviewCancelLecturer']);
});
