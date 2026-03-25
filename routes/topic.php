<?php

use App\Http\Controllers\Topic\TopicController;
use Illuminate\Support\Facades\Route;

// UC13: Tìm kiếm đề tài — tất cả user đã đăng nhập
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/topics', [TopicController::class, 'index']);
});

// UC14-16: Thêm / Sửa / Xoá — chỉ faculty_staff
Route::middleware('auth:sanctum')->prefix('faculty_staff')->group(function () {
    Route::post('/topics',          [TopicController::class, 'store']);   // UC14: Thêm đề tài
    Route::put('/topics/{id}',      [TopicController::class, 'update']);  // UC15: Sửa đề tài
    Route::delete('/topics/{id}',   [TopicController::class, 'destroy']); // UC16: Xoá đề tài
});
