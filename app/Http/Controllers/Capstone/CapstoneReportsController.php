<?php

namespace App\Http\Controllers\Capstone;

use App\Http\Controllers\Controller;
use App\Models\CapstoneReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CapstoneReportsController extends Controller
{
    // UC 21: Nộp báo cáo đồ án
    public function submitReport(Request $request)
    {
        $request->validate([
            'capstone_id' => 'required|exists:capstones,id',
            'report_file' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $filePath = $request->file('report_file')->store('reports', 'public');

        $report = CapstoneReport::create([
            'capstone_id'  => $request->capstone_id,
            'report_file'  => $filePath,
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message'      => 'Báo cáo đã được nộp thành công.',
            'report_id'    => $report->id,
            'file_path'    => Storage::url($filePath),
            'submitted_at' => $report->submitted_at,
        ], 201);
    }
}
