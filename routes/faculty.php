<?php

use App\Http\Controllers\Faculty\MilestoneController;
use App\Http\Controllers\Faculty\SemesterController;
use Illuminate\Support\Facades\Route;

Route::prefix('faculty')
    ->middleware(['auth:sanctum', 'role:faculty_staff'])
    ->group(function () {

        // UC: Lấy danh sách học kỳ - chỉ văn phòng khoa
        Route::get('/semesters', [SemesterController::class, 'index']);

        // UC: Thêm học kỳ - chỉ văn phòng khoa
        Route::post('/semesters', [SemesterController::class, 'store']);

        // UC: Thêm mốc thời gian - chỉ văn phòng khoa
        Route::post('/milestones', [MilestoneController::class, 'store']);

        // UC: Sửa mốc thời gian - chỉ văn phòng khoa
        Route::put('/milestones/{milestone}', [MilestoneController::class, 'update']);
    });