<?php

namespace App\Http\Resources;

use App\Models\Capstone;
use App\Models\Internship;
use App\Models\LecturerLeave;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    protected string $role;

    public function __construct($resource, string $role = '')
    {
        parent::__construct($resource);
        $this->role = $role;
    }

    public function toArray($request): array
    {
        return match ($this->role) {
            'student'    => $this->studentData(),
            'lecturer'   => $this->lecturerData(),
            'vpk'        => $this->vpkData(),
            'admin'      => $this->adminData(),
            'enterprise' => $this->enterpriseData(),
            default      => [],
        };
    }

    // ─── SINH VIÊN ───────────────────────────────────────────
    private function studentData(): array
    {
        $studentId = $this->student_id;
        $statuses  = $this->getStudentStatuses($studentId);

        return [
            'student_id' => $this->usercode,
            'full_name'  => $this->full_name,
            'gender'     => $this->gender,
            'dob'        => $this->dob,
            'class'      => $this->relationLoaded('class')
                                ? ($this->class?->class_name ?? null)
                                : null,
            'email'      => $this->email,
            'gpa'        => $this->gpa,
            'statuses'   => $statuses,
        ];
    }

    private function getStudentStatuses(int $studentId): array
    {
        $statuses = [];

        $capstone = Capstone::where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->first();

        if ($capstone) {
            if (in_array($capstone->status, [
                'INITIALIZED', 'LECTURER_APPROVED', 'TOPIC_APPROVED',
                'REPORTING', 'OFFICIAL_SUBMITTED', 'REVIEW_ELIGIBLE', 'DEFENSE_ELIGIBLE'
            ])) {
                $statuses[] = 'Đang làm đồ án';
            } elseif ($capstone->status === 'COMPLETED') {
                $statuses[] = 'Đã xong đồ án';
            } elseif ($capstone->status === 'FAILED') {
                $statuses[] = 'Trượt đồ án';
            }
        }

        $internship = Internship::where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->first();

        if ($internship) {
            if (in_array($internship->status, [
                'INITIALIZED', 'LECTURER_APPROVED', 'COMPANY_APPROVED', 'INTERNING'
            ])) {
                $statuses[] = 'Đang làm thực tập';
            } elseif ($internship->status === 'COMPLETED') {
                $statuses[] = 'Đã xong thực tập';
            } elseif ($internship->status === 'FAILED') {
                $statuses[] = 'Trượt thực tập';
            }
        }

        if (empty($statuses)) {
            $statuses[] = 'Chưa có hoạt động';
        }

        return $statuses;
    }

    // ─── GIẢNG VIÊN ──────────────────────────────────────────
    private function lecturerData(): array
    {
        $lecturerId = $this->lecturer_id;

        // ← Fix: kiểm tra relationship đã load chưa trước khi map
        $expertises = $this->relationLoaded('lecturerExpertises')
            ? $this->lecturerExpertises
                ->map(fn($le) => $le->expertise?->name)
                ->filter()
                ->values()
                ->toArray()
            : [];

        $capstoneCount = Capstone::where('lecturer_id', $lecturerId)
            ->whereNotIn('status', ['COMPLETED', 'FAILED', 'CANCEL'])
            ->count();

        $internshipCount = Internship::where('lecturer_id', $lecturerId)
            ->whereNotIn('status', ['COMPLETED', 'FAILED', 'CANCEL'])
            ->count();

        $status = $this->getLecturerStatus($lecturerId);

        return [
            'lecturer_id'      => $this->usercode,
            'full_name'        => $this->full_name,
            'gender'           => $this->gender,
            'dob'              => $this->dob,
            'degree'           => $this->degree,
            'email'            => $this->email,
            'phone_number'     => $this->phone_number,
            'expertises'       => $expertises,
            'department'       => $this->department,
            'status'           => $status,
            'capstone_count'   => $capstoneCount,
            'internship_count' => $internshipCount,
        ];
    }

    private function getLecturerStatus(int $lecturerId): string
    {
        $activeLeave = LecturerLeave::whereHas('request', fn($q) =>
            $q->where('lecturer_id', $lecturerId))
            ->whereIn('status', ['LEAVE_ACTIVE', 'APPROVED_PENDING'])
            ->exists();

        if ($activeLeave) return 'Nghỉ phép';

        return 'Có nhận';
    }

    // ─── VĂN PHÒNG KHOA ──────────────────────────────────────
    private function vpkData(): array
    {
        return [
            'staff_id'  => $this->usercode,
            'full_name' => $this->full_name,
            'email'     => $this->email,
            'gender'    => $this->gender,
            'dob'       => $this->dob,
        ];
    }

    // ─── ADMIN ───────────────────────────────────────────────
    private function adminData(): array
    {
        return [
            'staff_id'  => $this->usercode,
            'full_name' => $this->full_name,
            'email'     => $this->email,
            'gender'    => $this->gender ?? null,
            'dob'       => $this->dob ?? null,
        ];
    }

    // ─── DOANH NGHIỆP ────────────────────────────────────────
    private function enterpriseData(): array
    {
        return [
            'name'    => $this->name,
            'email'   => $this->email,
            'address' => $this->address,
            'website' => $this->website,
        ];
    }
}