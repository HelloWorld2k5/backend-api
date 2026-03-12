<?php

use App\Http\Controllers\Lecturer\LecturerController;
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
