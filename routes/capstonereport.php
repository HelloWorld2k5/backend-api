<?php

use App\Http\Controllers\Capstone\CapstoneReportsController;
use Illuminate\Support\Facades\Route;

// UC 21: Nộp báo cáo đồ án
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/capstones/reports', [CapstoneReportsController::class, 'submitReport']);
});
