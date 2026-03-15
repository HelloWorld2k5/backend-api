<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Requests\Faculty\StoreSemesterRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;

class SemesterController extends Controller
{
    /**
     * UC: Thêm năm học và học kỳ mới
     * Chỉ Văn phòng Khoa (faculty_staff) mới được phép thực hiện.
     */
    public function store(StoreSemesterRequest $request): JsonResponse
    {
        $yearName     = $request->input('year_name');
        $semesterName = $request->input('semester_name');

        // Tìm hoặc tạo năm học theo year_name
        $academicYear = AcademicYear::firstOrCreate(
            ['year_name' => $yearName],
            [
                // Tự parse start_year/end_year từ chuỗi năm học (vd: "2024-2025")
                'start_year' => explode('-', $yearName)[0] ?? null,
                'end_year'   => explode('-', $yearName)[1] ?? null,
            ]
        );

        $semester = Semester::create([
            'year_id'       => $academicYear->year_id,
            'semester_name' => $semesterName,
            'start_date'    => $request->input('start_date'),
            'end_date'      => $request->input('end_date'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm học kỳ thành công.',
            'data'    => [
                'academic_year' => $academicYear,
                'semester'      => $semester,
            ],
        ], 201);
    }
    /**
     * UC: Lấy danh sách tất cả học kỳ
     * Chỉ Văn phòng Khoa (faculty_staff) mới được phép thực hiện.
     */
    public function index(): JsonResponse
    {
        // Eager load Mối quan hệ AcademicYear 
        // Sắp xếp theo Năm học giảm dần, Học kỳ giảm dần
        $semesters = Semester::with('academicYear')
            ->join('academic_years', 'semesters.year_id', '=', 'academic_years.year_id')
            ->orderBy('academic_years.year_name', 'desc')
            ->orderBy('semesters.semester_name', 'desc')
            ->select('semesters.*')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách học kỳ thành công.',
            'data'    => $semesters,
        ], 200);
    }
}
