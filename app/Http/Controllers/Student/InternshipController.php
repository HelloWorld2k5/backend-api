<?php

namespace App\Http\Controllers\Internship;

use App\Http\Controllers\Controller;
use App\Models\Capstone;
use App\Models\Milestone;
use App\Models\Company;
use App\Models\ProposedCompany;
use App\Models\InternshipRequest;
use App\Http\Requests\Internship\RegisterCompanyRequest;
use App\Http\Resources\CapstoneResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternshipController extends Controller
{
    /**
     * UC 33: Đăng ký đợt thực tập
     */
    public function registerSession()
    {
        $studentId = auth()->id();

        // Bước 3: Kiểm tra điều kiện thời gian (Milestone)
        $milestone = Milestone::where('type', Milestone::TYPE_INTERNSHIP)->upcoming()->first();
        if (!$milestone) {
            return response()->json(['success' => false, 'message' => 'Đã hết hạn đăng ký thực tập (3b)'], 400);
        }

        // Ngoại lệ 3a: Kiểm tra xem đã đăng ký chưa (BR-1)
        $exists = Capstone::where('student_id', $studentId)
            ->where('semester_id', $milestone->semester_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Bạn đã đăng ký đợt thực tập (3a)'], 400);
        }

        // Bước 4: Tạo bản ghi đồ án khởi tạo
        $capstone = Capstone::create([
            'student_id'  => $studentId,
            'semester_id' => $milestone->semester_id,
            'status'      => Capstone::STATUS_INITIALIZED,
            'topic_id'    => 1 // Giả định ID mặc định để tránh lỗi NOT NULL
        ]);

        return response()->json(['success' => true, 'message' => 'Đã đăng ký đợt thực tập thành công (Bước 5)']);
    }

    /**
     * UC 34 - Bước 5 & 6: Kiểm tra Mã số thuế
     */
    public function checkTaxCode(Request $request)
    {
        $taxCode = $request->query('tax_code');

        // Tìm trong DN chính thức
        $official = Company::where('usercode', $taxCode)->first();
        if ($official) {
            return response()->json(['type' => 'OFFICIAL', 'data' => $official, 'readonly' => true]);
        }

        // Tìm trong DN đề xuất
        $proposed = ProposedCompany::where('tax_code', $taxCode)->first();
        if ($proposed) {
            return response()->json(['type' => 'PROPOSED', 'data' => $proposed, 'readonly' => false]);
        }

        return response()->json(['type' => 'NEW', 'readonly' => false]);
    }

    /**
     * UC 34 - Bước 8-11: Lưu thông tin doanh nghiệp
     */
    public function registerCompany(RegisterCompanyRequest $request)
    {
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
                        'name' => $request->name,
                        'address' => $request->address,
                        'contact_email' => $request->email
                    ]
                );
                $proposedId = $proposed->proposed_company_id;
            }

            // Lưu file minh chứng
            $path = $request->file('file')->store('internship/proofs', 'public');

            // Tạo yêu cầu đăng ký (Bước 10)
            InternshipRequest::create([
                'internship_id'       => $request->capstone_id,
                'company_id'          => $companyId,
                'proposed_company_id' => $proposedId,
                'type'                => InternshipRequest::TYPE_COMPANY_REG,
                'status'              => InternshipRequest::STATUS_PENDING_FACULTY,
                'file_path'           => $path,
                'student_message'     => $request->position,
            ]);

            return response()->json(['success' => true, 'message' => 'Đăng ký doanh nghiệp thành công (Bước 11)']);
        });
    }
}
