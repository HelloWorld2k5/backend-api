<?php

// use App\Http\Controllers\Lecturer\ProfileController;
use App\Http\Controllers\Lecturer\ProfileController;
use App\Http\Controllers\Lecturer\LeaveRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('lecturer')
    ->middleware(['auth:sanctum', 'role:lecturer'])
    ->group(function () {
        Route::get('/expertises', [ProfileController::class, 'getExpertises']);//  Lấy danh sách | đang 
        Route::put('/expertises', [ProfileController::class, 'updateExpertises']); // Cập nhật
        // UC7 - Yêu cầu nghỉ phép dài hạn-đang ở routes không phù hợp
        Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    });