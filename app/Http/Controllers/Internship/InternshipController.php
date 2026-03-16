<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Models\{Internship, Milestone, Company, ProposedCompany, InternshipRequest};
use App\Http\Requests\Internship\RegisterInternshipRequest;
use App\Http\Resources\Internship\InternshipResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InternshipController extends Controller
{
    public function register(RegisterInternshipRequest $request)
    {
        $studentId = auth()->id();
        $milestone = Milestone::findOrFail($request->milestone_id);

        // 1. Ngoại lệ 3b: Kiểm tra hết hạn đăng ký
        if (Carbon::now()->gt($milestone->deadline)) {
            return response()->json([
                'success' => false,
                'message' => 'Đã hết hạn đăng ký thực tập.'
            ], 400);
        }

        // 2. Ngoại lệ 3a (BR-1): Kiểm tra đã đăng ký thực tập trước đó trong học kỳ này chưa
        $alreadyRegistered = Internship::where('student_id', $studentId)
            ->where('semester_id', $milestone->semester_id)
            ->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đăng ký đợt thực tập.'
            ], 400);
        }

        // 3. Luồng chính: Tạo bản ghi thực tập mới
        return DB::transaction(function () use ($studentId, $milestone) {
            $internship = Internship::create([
                'student_id'  => $studentId,
                'semester_id' => $milestone->semester_id,
                'status'      => 'INITIALIZED', // Trạng thái khởi tạo bản ghi
            ]);

            return (new InternshipResource($internship))
                ->additional(['message' => 'Đã đăng ký đợt thực tập thành công.']);
        });
    }
    /**
     * UC 34 - Bước 5 & 6: Kiểm tra Mã số thuế trong DB
     */
    public function checkCompany(Request $request)
    {
        $taxCode = $request->query('tax_code');

        // Tìm trong DN chính thức (Dùng usercode làm MST theo Model Company)
        $official = Company::where('usercode', $taxCode)->first();
        if ($official) {
            return response()->json([
                'exists'   => true,
                'type'     => 'OFFICIAL',
                'readonly' => true, // BR-1
                'data'     => $official
            ]);
        }

        // Tìm trong DN do sinh viên đề xuất
        $proposed = ProposedCompany::where('tax_code', $taxCode)->first();
        if ($proposed) {
            return response()->json([
                'exists'   => true,
                'type'     => 'PROPOSED',
                'readonly' => false, // BR-1
                'data'     => $proposed
            ]);
        }

        return response()->json(['exists' => false, 'type' => 'NEW', 'readonly' => false]);
    }

    /**
     * UC 34 - Bước 8-11: Lưu thông tin đăng ký doanh nghiệp
     */
    public function registerCompany(RegisterCompanyRequest $request)
    {
        $studentId = auth()->id();
        // Bước 9: Kiểm tra tính hợp lệ về thời gian (BR-3)
        $milestone = Milestone::where('type', Milestone::TYPE_INTERNSHIP)->upcoming()->first();
        if (!$milestone) {
            return response()->json(['message' => 'Đã hết hạn đăng ký doanh nghiệp (BR-3)'], 400);
        }

        return DB::transaction(function () use ($request) {
            $companyId = null;
            $proposedId = null;

            // Xử lý thông tin doanh nghiệp (BR-1)
            $official = Company::where('usercode', $request->tax_code)->first();
            if ($official) {
                $companyId = $official->company_id;
            } else {
                $proposed = ProposedCompany::updateOrCreate(
                    ['tax_code' => $request->tax_code],
                    [
                        'name'          => $request->name,
                        'address'       => $request->address,
                        'contact_email' => $request->email,
                    ]
                );
                $proposedId = $proposed->proposed_company_id;
            }

            // Upload file minh chứng (Bước 7)
            $path = $request->file('file')->store('internship/proofs', 'public');

            // Bước 10: Lưu bản ghi đăng ký
            $internReq = InternshipRequest::create([
                'internship_id'       => $request->internship_id,
                'company_id'          => $companyId,
                'proposed_company_id' => $proposedId,
                'type'                => InternshipRequest::TYPE_COMPANY_REG,
                'status'              => InternshipRequest::STATUS_PENDING_FACULTY,
                'student_message'     => $request->position, // Lưu vị trí thực tập
                'file_path'           => $path,
            ]);

            return (new InternshipRequestResource($internReq))
                ->additional(['success' => true, 'message' => 'Đăng ký doanh nghiệp thành công (Bước 11)']);
        });
    }
    /**
     * UC 42 - Bước 2: Hiển thị danh sách doanh nghiệp chờ duyệt
     */
    public function getPendingRequests()
    {
        // BR-1: Chức năng duyệt chỉ mở sau khi đóng cổng đăng ký
        $isClosed = Milestone::where('type', Milestone::TYPE_INTERNSHIP)
            ->where('deadline', '<', Carbon::now())
            ->exists();

        if (!$isClosed) {
            return response()->json(['message' => 'Cổng đăng ký của sinh viên chưa đóng (BR-1).'], 400);
        }

        $requests = InternshipRequest::with(['company', 'proposedCompany', 'internship.student'])
            ->where('status', InternshipRequest::STATUS_PENDING_FACULTY)
            ->get();

        return CompanyPendingResource::collection($requests);
    }

    /**
     * UC 42 - Bước 6-10: Xử lý Duyệt hoặc Từ chối
     */
    public function approveRequest(ApproveCompanyRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $internReq = InternshipRequest::findOrFail($id);

            if ($request->status === InternshipRequest::STATUS_APPROVED) {
                // BR-2: Xử lý cấp tài khoản doanh nghiệp nếu chưa có
                if (!$internReq->company_id && $internReq->proposed_company_id) {
                    $proposed = $internReq->proposedCompany;

                    $newCompany = Company::create([
                        'usercode'     => $proposed->tax_code,
                        'name'         => $request->company_name ?? $proposed->name,
                        'email'        => $request->company_email ?? $proposed->contact_email,
                        'address'      => $request->company_address ?? $proposed->address,
                        'password'     => Hash::make($proposed->tax_code), // Pass mặc định là MST
                        'is_active'    => true,
                        'is_partnered' => true,
                    ]);

                    $internReq->company_id = $newCompany->company_id;
                }

                // Cập nhật trạng thái yêu cầu
                $internReq->update(['status' => InternshipRequest::STATUS_APPROVED]);

                // Cập nhật thông tin doanh nghiệp vào bản ghi Internship của các SV được chọn (BR-4)
                $internReq->internship()->update([
                    'company_id' => $internReq->company_id,
                    'status'     => 'COMPANY_APPROVED'
                ]);

                // Bước 9: Gửi email (Giả định có Class Mail)
                // Mail::to($internReq->company->email)->send(new InternshipResultMail($request->status));

            } else {
                // 6a: Từ chối
                $internReq->update([
                    'status'   => InternshipRequest::STATUS_REJECTED,
                    'feedback' => $request->feedback
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Duyệt doanh nghiệp thành công (Bước 10)']);
        });
    }
    /**
     * UC 35 - Bước 4: Lấy lịch sử nộp báo cáo của sinh viên
     */
    public function getReportHistory(Request $request)
    {
        $studentId = auth()->id(); // Hoặc thay bằng 1 để test nhanh
        $milestoneId = $request->query('milestone_id');

        $reports = InternshipReport::whereHas('internship', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })
            ->where('milestone_id', $milestoneId)
            ->orderBy('submission_date', 'desc')
            ->get();

        return InternshipReportResource::collection($reports);
    }

    /**
     * UC 35 - Bước 6-9: Thực hiện nộp báo cáo
     */
    public function submitReport(SubmitReportRequest $request)
    {
        $studentId = auth()->id(); // Hoặc thay bằng 1 để test nhanh

        // Tiền điều kiện: Sinh viên phải có bản ghi thực tập và đã có GVHD
        $internship = Internship::where('student_id', $studentId)->first();
        if (!$internship || !$internship->lecturer_id) {
            return response()->json(['message' => 'Bạn chưa được phân công giảng viên hướng dẫn.'], 403);
        }

        $milestone = Milestone::findOrFail($request->milestone_id);

        // 3a & 8a1: Kiểm tra thời hạn nộp bài
        if (Carbon::now()->gt($milestone->deadline)) {
            return response()->json(['message' => 'Đã hết thời gian nộp (3a).'], 400);
        }

        // 3b & 8a2: Kiểm tra số lần nộp (Tối đa 5 lần - BR-2)
        $submissionCount = InternshipReport::where('internship_id', $internship->internship_id)
            ->where('milestone_id', $milestone->milestone_id)
            ->count();

        if ($submissionCount >= 5) {
            return response()->json(['message' => 'Bạn đã nộp tối đa 5 lần cho hạng mục này (3b).'], 400);
        }

        return DB::transaction(function () use ($request, $internship, $milestone) {
            // Bước 8: Lưu trữ tệp tin
            $path = $request->file('file')->store('internship/reports', 'public');

            // Tạo bản ghi báo cáo mới (Lưu lịch sử các version cũ - BR-2)
            $report = InternshipReport::create([
                'internship_id'   => $internship->internship_id,
                'milestone_id'    => $milestone->milestone_id,
                'status'          => InternshipReport::STATUS_PENDING,
                'description'     => $request->description,
                'file_path'       => $path,
                'submission_date' => Carbon::now(),
            ]);

            return (new InternshipReportResource($report))
                ->additional(['success' => true, 'message' => 'Nộp bài thành công (Bước 9)']);
        });
    }
    /**
     * UC 40 - Bước 3: Danh sách báo cáo cần duyệt (Dành cho GV)
     */
    public function getReportsToReview()
    {
        $lecturerId = auth()->id();
        $lecturer = Lecturer::findOrFail($lecturerId);

        // Ngoại lệ 2a: Kiểm tra trạng thái nghỉ phép
        if ($lecturer->is_on_leave) { // Giả định cột is_on_leave trong bảng lecturers
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể truy cập chức năng này khi đang trong trạng thái nghỉ phép.'
            ], 403);
        }

        // BR-1: Chỉ lấy các báo cáo đang ở trạng thái PENDING (chưa duyệt)
        $reports = InternshipReport::whereHas('internship', function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId);
        })
            ->where('status', InternshipReport::STATUS_PENDING)
            ->with(['internship.student', 'milestone'])
            ->get();

        return ReportReviewResource::collection($reports);
    }

    /**
     * UC 40 - Bước 7-9: Xử lý Duyệt hoặc Từ chối
     */
    public function reviewReport(ReviewReportRequest $request, $id)
    {
        $lecturerId = auth()->id();

        $report = InternshipReport::whereHas('internship', function ($q) use ($lecturerId) {
            $q->where('lecturer_id', $lecturerId);
        })->findOrFail($id);

        // Cập nhật trạng thái và nhận xét (Bước 8)
        $report->update([
            'status'            => $request->status,
            'lecturer_feedback' => $request->feedback,
            'updated_at'        => Carbon::now()
        ]);

        // Bước 9: Gửi thông báo cho sinh viên (Có thể dùng Queue/Notification)
        // Notification::send($report->internship->student, new ReportReviewedNotification($report));

        return response()->json([
            'success' => true,
            'message' => $request->status === 'APPROVED' ? 'Đã duyệt báo cáo thành công.' : 'Đã từ chối báo cáo.',
            'data'    => new ReportReviewResource($report)
        ]);
    }
}
