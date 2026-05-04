<?php

namespace App\Services;

use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use RuntimeException;
use Throwable;

class PracticumService
{
    public function rows(array $rows): array
    {
        return array_map(static fn ($row) => (array)$row, $rows);
    }

    public function row(object|array|null $row): ?array
    {
        return $row ? (array)$row : null;
    }

    public function currentUser(): ?array
    {
        return session('user');
    }

    public function requireRole(string|array $roles): array
    {
        $user = $this->currentUser();
        if (!$user) {
            abort(redirect()->route('login'));
        }
        if (!empty($user['id'])) {
            $freshUser = $this->userFind((int)$user['id']);
            if ($freshUser) {
                $user = array_merge($user, ['name' => $freshUser['name'], 'email' => $freshUser['email'], 'role' => $freshUser['role'], 'password_changed' => (int)($freshUser['password_changed'] ?? 1), 'is_active' => (int)($freshUser['is_active'] ?? 1)]);
                session(['user' => $user]);
            }
        }
        if (!in_array($user['role'] ?? '', (array)$roles, true)) {
            abort(403, 'Forbidden');
        }
        $path = trim(request()->path(), '/');
        $isPasswordRoute = request()->routeIs('student.password.edit', 'student.password.update') || str_ends_with($path, 'student/change-password');
        $isStudentProfileRoute = request()->routeIs('student.profile', 'student.profile.save') || str_ends_with($path, 'student/profile');

        if (($user['role'] ?? '') !== 'admin' && (int)($user['password_changed'] ?? 1) === 0 && !$isPasswordRoute) {
            abort(redirect()->route('student.password.edit'));
        }
        if (($user['role'] ?? '') === 'student' && !$isStudentProfileRoute && !$isPasswordRoute) {
            $student = $this->studentFindByUser((int)$user['id']);
            if ($student && (int)($student['profile_completed'] ?? 0) === 0) {
                abort(redirect()->route('student.profile'));
            }
        }
        return $user;
    }

    public function routeForRole(?string $role = null): string
    {
        return match ($role ?? ($this->currentUser()['role'] ?? '')) {
            'admin' => 'admin.dashboard',
            'coordinator' => 'coordinator.dashboard',
            'student' => 'student.dashboard',
            'partner' => 'partner.dashboard',
            default => 'login',
        };
    }

    public function randomPassword(int $length = 12): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@$%';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $password;
    }

    public function projectedOjtEndDate(string $startDate, int $requiredHours, int $hoursPerDay = 8): string
    {
        $daysNeeded = max(1, (int)ceil($requiredHours / max(1, $hoursPerDay)));
        $date = new DateTimeImmutable($startDate);
        $workedDays = 0;
        while ($workedDays < $daysNeeded) {
            if ((int)$date->format('N') <= 5) {
                $workedDays++;
            }
            if ($workedDays < $daysNeeded) {
                $date = $date->modify('+1 day');
            }
        }
        return $date->format('Y-m-d');
    }

    public function uploadDocument(?object $file, string $folder = 'documents', bool $required = true): ?string
    {
        if (!$file || !$file->isValid()) {
            if ($required) {
                throw new RuntimeException('Document upload is required.');
            }
            return null;
        }
        if ($file->getSize() > 8 * 1024 * 1024) {
            throw new RuntimeException('Uploaded file must not exceed 8MB.');
        }
        $allowed = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];
        $mime = $file->getMimeType();
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Upload must be a PDF, JPG, or PNG file.');
        }
        $safeFolder = preg_replace('/[^a-z0-9_\/-]/i', '', $folder) ?: 'documents';
        $targetDir = public_path('uploads/' . $safeFolder);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $file->move($targetDir, $name);
        return 'uploads/' . $safeFolder . '/' . $name;
    }

    public function uploadWeeklyReport(?object $file): string
    {
        if (!$file || !$file->isValid()) {
            throw new RuntimeException('Report file is required.');
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new RuntimeException('Report file must not exceed 5MB.');
        }
        if ($file->getMimeType() !== 'application/pdf') {
            throw new RuntimeException('Weekly report upload must be PDF.');
        }
        $targetDir = public_path('uploads/reports');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $name = bin2hex(random_bytes(16)) . '.pdf';
        $file->move($targetDir, $name);
        return 'uploads/reports/' . $name;
    }

    public function userFindByEmail(string $email): ?array { return $this->row(DB::selectOne('SELECT * FROM users WHERE email = ? LIMIT 1', [strtolower(trim($email))])); }
    public function userFind(int $id): ?array { return $this->row(DB::selectOne('SELECT * FROM users WHERE id = ? LIMIT 1', [$id])); }
    public function userCreate(string $name, string $email, string $password, string $role, ?int $createdBy = null, int $passwordChanged = 1): int
    {
        return (int)DB::table('users')->insertGetId(['name' => $name, 'email' => strtolower(trim($email)), 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => $role, 'created_by' => $createdBy, 'is_active' => 1, 'password_changed' => $passwordChanged]);
    }
    public function usersAllStudents(): array { return $this->rows(DB::select('SELECT u.*, s.student_no, s.course FROM users u JOIN students s ON s.user_id = u.id ORDER BY u.name ASC')); }
    public function usersByRole(string $role): array { return $this->rows(DB::select('SELECT * FROM users WHERE role = ? ORDER BY name', [$role])); }
    public function userSetActive(int $id, int $active): void { DB::update('UPDATE users SET is_active = ? WHERE id = ?', [$active, $id]); }
    public function userUpdatePassword(int $id, string $password, int $passwordChanged = 1): void { DB::update('UPDATE users SET password_hash = ?, password_changed = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $passwordChanged, $id]); }
    public function userCountRole(string $role): int { return (int)DB::scalar('SELECT COUNT(*) FROM users WHERE role = ? AND is_active = 1', [$role]); }

    public function companyCreate(int $userId, string $name, string $address, string $contactPerson, string $contactEmail, string $contactNumber = '', array $programIds = []): int
    {
        $companyId = (int)DB::table('partner_companies')->insertGetId(['user_id' => $userId, 'name' => $name, 'address' => $address, 'contact_person' => $contactPerson, 'contact_email' => strtolower(trim($contactEmail)), 'contact_number' => $contactNumber]);
        $this->companySyncPrograms($companyId, $programIds);
        return $companyId;
    }
    public function companiesAll(): array { return $this->rows(DB::select('SELECT pc.id, pc.user_id, pc.name, pc.address, pc.contact_person, pc.contact_email, pc.contact_number, pc.created_at, u.id user_id_key, u.email, u.is_active, cp.accepted_programs, cp.accepted_program_ids FROM partner_companies pc JOIN users u ON u.id = pc.user_id LEFT JOIN (SELECT cp.company_id, GROUP_CONCAT(p.code ORDER BY p.code SEPARATOR ", ") accepted_programs, GROUP_CONCAT(cp.program_id ORDER BY cp.program_id SEPARATOR ",") accepted_program_ids FROM company_programs cp LEFT JOIN programs p ON p.id = cp.program_id GROUP BY cp.company_id) cp ON cp.company_id = pc.id ORDER BY pc.name')); }
    public function companyFind(int $id): ?array { return $this->row(DB::selectOne('SELECT pc.*, u.email user_email FROM partner_companies pc JOIN users u ON u.id = pc.user_id WHERE pc.id = ?', [$id])); }
    public function companyFindByUser(int $userId): ?array { return $this->row(DB::selectOne('SELECT * FROM partner_companies WHERE user_id = ? LIMIT 1', [$userId])); }
    public function companyCount(): int { return (int)DB::scalar('SELECT COUNT(*) FROM partner_companies'); }
    public function companySyncPrograms(int $companyId, array $programIds): void
    {
        DB::table('company_programs')->where('company_id', $companyId)->delete();
        foreach (array_unique(array_map('intval', $programIds)) as $programId) {
            if ($programId > 0) DB::table('company_programs')->insert(['company_id' => $companyId, 'program_id' => $programId]);
        }
    }
    public function companyAcceptsProgram(int $companyId, int $programId): bool { return DB::table('partner_companies')->where('id', $companyId)->exists(); }

    public function programsAll(bool $activeOnly = false): array
    {
        $query = DB::table('programs');
        if ($activeOnly) $query->where('is_active', 1);
        return $this->rows($query->orderBy('code')->get()->all());
    }
    public function programFind(int $id): ?array { return $this->row(DB::table('programs')->where('id', $id)->first()); }
    public function programCreate(string $code, string $name, int $requiredHours): int { return (int)DB::table('programs')->insertGetId(['code' => strtoupper(trim($code)), 'name' => trim($name), 'required_hours' => $requiredHours]); }
    public function programUpdate(int $id, string $code, string $name, int $requiredHours, int $active): void { DB::table('programs')->where('id', $id)->update(['code' => strtoupper(trim($code)), 'name' => trim($name), 'required_hours' => $requiredHours, 'is_active' => $active]); }
    public function programDelete(int $id): void { DB::table('programs')->where('id', $id)->delete(); }

    public function studentCreate(int $userId, string $studentNo, string $course, string $yearLevel, string $corFile, int $coordinatorId, ?int $programId = null, string $section = ''): int
    {
        return (int)DB::table('students')->insertGetId(['user_id' => $userId, 'student_no' => $studentNo, 'program_id' => $programId, 'course' => $course, 'year_level' => $yearLevel, 'section' => $section, 'cor_file' => $corFile, 'coordinator_id' => $coordinatorId]);
    }
    public function studentsAllByCoordinator(int $coordinatorUserId): array { return $this->rows(DB::select('SELECT s.*, u.name, u.email, u.is_active, p.code program_code, p.required_hours program_required_hours, e.id enrollment_id, e.status deployment_status, e.predeployment_status, e.required_hours, COALESCE(dt.rendered_hours, 0) rendered_hours, pc.name company_name FROM students s JOIN users u ON u.id = s.user_id LEFT JOIN programs p ON p.id = s.program_id LEFT JOIN ojt_enrollments e ON e.student_id = s.id LEFT JOIN (SELECT student_id, SUM(hours) rendered_hours FROM daily_time_records GROUP BY student_id) dt ON dt.student_id = s.id LEFT JOIN partner_companies pc ON pc.id = e.company_id WHERE s.coordinator_id = ? ORDER BY u.name', [$coordinatorUserId])); }
    public function studentFind(int $id): ?array { return $this->row(DB::selectOne('SELECT s.*, u.name, u.email, u.is_active, c.name coordinator_name, c.email coordinator_email FROM students s JOIN users u ON u.id = s.user_id LEFT JOIN users c ON c.id = s.coordinator_id WHERE s.id = ?', [$id])); }
    public function studentFindByUser(int $userId): ?array { return $this->row(DB::selectOne('SELECT s.*, u.name, u.email, c.name coordinator_name, c.email coordinator_email FROM students s JOIN users u ON u.id = s.user_id LEFT JOIN users c ON c.id = s.coordinator_id WHERE s.user_id = ?', [$userId])); }
    public function studentCountByCoordinator(int $coordinatorUserId): int { return (int)DB::scalar('SELECT COUNT(*) FROM students WHERE coordinator_id = ?', [$coordinatorUserId]); }
    public function studentUpdateProfile(int $studentId, array $data, ?string $photoFile): void
    {
        DB::table('students')->where('id', $studentId)->update(['address' => trim($data['address'] ?? ''), 'contact_number' => trim($data['contact_number'] ?? ''), 'emergency_contact_name' => trim($data['emergency_contact_name'] ?? ''), 'emergency_contact_number' => trim($data['emergency_contact_number'] ?? ''), 'guardian_name' => trim($data['guardian_name'] ?? ''), 'guardian_contact' => trim($data['guardian_contact'] ?? ''), 'year_level' => trim($data['year_level'] ?? ''), 'section' => trim($data['section'] ?? ''), 'profile_completed' => 1] + ($photoFile ? ['photo_file' => $photoFile] : []));
    }
    public function requirementDefinitions(): array
    {
        return ['guardian_consent' => ['name' => 'Parent/Guardian Consent Form', 'notes' => 'Download the template, fill it out, have it signed and notarized, then upload the scanned copy.'], 'philhealth' => ['name' => 'PhilHealth Card / Document', 'notes' => 'Upload scan or photo.'], 'vaccine_card' => ['name' => 'Vaccine Card', 'notes' => 'Upload scan or photo.'], 'guardian_id' => ['name' => "Guardian's Valid ID", 'notes' => 'Upload scan or photo.'], 'cor' => ['name' => 'Certificate of Registration (COR)', 'notes' => 'Upload current term COR.']];
    }
    public function normalizePredeploymentStatus(?string $status): string
    {
        $status = trim((string)$status);
        return in_array($status, ['not_submitted', 'submitted', 'approved', 'needs_revision', 'forwarded', 'accepted', 'orientation_scheduled', 'orientation_completed'], true)
            ? $status
            : 'not_submitted';
    }
    public function effectivePredeploymentStatusForStudent(int $studentId, ?string $currentStatus = null, ?array $requirements = null): string
    {
        $currentStatus = $this->normalizePredeploymentStatus($currentStatus);
        if (in_array($currentStatus, ['forwarded', 'accepted', 'orientation_scheduled', 'orientation_completed'], true)) {
            return $currentStatus;
        }

        $requirements ??= $this->studentRequirements($studentId);
        $hasRejected = false;
        $allApproved = true;

        foreach ($requirements as $requirement) {
            $hasFile = !empty($requirement['file_path']);
            $status = trim((string)($requirement['status'] ?? 'pending'));

            if ($hasFile && $status === 'rejected') {
                $hasRejected = true;
            }

            if (!$hasFile || $status !== 'approved') {
                $allApproved = false;
            }
        }

        if ($allApproved) {
            return 'approved';
        }

        if ($hasRejected) {
            return 'needs_revision';
        }

        return $currentStatus;
    }
    public function studentRequirements(int $studentId): array
    {
        $defs = $this->requirementDefinitions(); $rows = [];
        foreach ($this->rows(DB::select('SELECT * FROM student_requirements WHERE student_id = ?', [$studentId])) as $row) $rows[$row['requirement_key']] = $row;
        foreach ($defs as $key => $def) {
            if (!isset($rows[$key])) $rows[$key] = ['requirement_key' => $key, 'requirement_name' => $def['name'], 'notes' => $def['notes'], 'file_path' => null, 'status' => 'pending'];
            else { $rows[$key]['review_notes'] = $rows[$key]['notes'] ?? ''; $rows[$key]['notes'] = $def['notes']; }
        }
        return $rows;
    }
    public function studentRequirement(int $studentId, string $key): array
    {
        $requirements = $this->studentRequirements($studentId);
        if (!isset($requirements[$key])) throw new RuntimeException('Invalid requirement.');
        return $requirements[$key];
    }
    public function studentCanUploadRequirement(int $studentId, string $key): bool
    {
        $requirement = $this->studentRequirement($studentId, $key);
        $status = $requirement['status'] ?? 'pending';
        $hasFile = !empty($requirement['file_path']);
        $predeploymentStatus = $this->normalizePredeploymentStatus($this->enrollmentDetailsByStudent($studentId)['predeployment_status'] ?? 'not_submitted');
        if (in_array($predeploymentStatus, ['approved', 'forwarded', 'accepted', 'orientation_scheduled', 'orientation_completed'], true)) return false;
        if ($status === 'rejected') return true;
        if ($hasFile) return false;
        if ($predeploymentStatus === 'submitted') return false;
        return true;
    }
    public function studentRequirementUploadMessage(int $studentId, string $key): string
    {
        $requirement = $this->studentRequirement($studentId, $key);
        $status = $requirement['status'] ?? 'pending';
        $hasFile = !empty($requirement['file_path']);
        $predeploymentStatus = $this->normalizePredeploymentStatus($this->enrollmentDetailsByStudent($studentId)['predeployment_status'] ?? 'not_submitted');
        if ($status === 'approved') return 'Approved';
        if ($status === 'uploaded' && $hasFile) return 'Awaiting review';
        if ($status === 'rejected') return 'Replace the rejected file';
        if (in_array($predeploymentStatus, ['approved', 'forwarded', 'accepted', 'orientation_scheduled', 'orientation_completed'], true)) return 'Locked';
        if ($predeploymentStatus === 'submitted') return 'Under review';
        if ($hasFile) return 'Already uploaded';
        return 'Ready to upload';
    }
    public function studentHasRejectedRequirements(int $studentId): bool { foreach ($this->studentRequirements($studentId) as $req) if (!empty($req['file_path']) && ($req['status'] ?? '') === 'rejected') return true; return false; }
    public function studentSaveRequirement(int $studentId, string $key, string $filePath): void
    {
        $defs = $this->requirementDefinitions();
        if (!isset($defs[$key])) throw new RuntimeException('Invalid requirement.');
        $existing = $this->row(DB::selectOne('SELECT status FROM student_requirements WHERE student_id = ? AND requirement_key = ? LIMIT 1', [$studentId, $key]));
        DB::statement('INSERT INTO student_requirements (student_id, requirement_key, requirement_name, file_path, status, uploaded_at) VALUES (?, ?, ?, ?, "uploaded", NOW()) ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), status = "uploaded", uploaded_at = NOW()', [$studentId, $key, $defs[$key]['name'], $filePath]);
        $enrollment = $this->enrollmentDetailsByStudent($studentId);
        if (($existing['status'] ?? '') === 'rejected' || ($enrollment['predeployment_status'] ?? '') === 'needs_revision') {
            $nextStatus = $this->studentHasRejectedRequirements($studentId)
                ? 'needs_revision'
                : ($this->studentHasCompleteRequirements($studentId) ? 'submitted' : 'not_submitted');
            $this->enrollmentSetPredeploymentStatus($studentId, $nextStatus);
        }
    }
    public function studentReviewRequirement(int $studentId, string $key, string $status, string $notes = ''): void
    {
        if (!in_array($status, ['approved', 'rejected'], true) || !isset($this->requirementDefinitions()[$key])) throw new RuntimeException('Invalid requirement review.');
        $count = DB::update('UPDATE student_requirements SET status = ?, notes = ?, reviewed_at = NOW() WHERE student_id = ? AND requirement_key = ? AND file_path IS NOT NULL', [$status, $notes, $studentId, $key]);
        if ($count === 0) throw new RuntimeException('Requirement file is not available for review.');
    }
    public function studentHasCompleteRequirements(int $studentId): bool { foreach ($this->studentRequirements($studentId) as $req) if (empty($req['file_path'])) return false; return true; }
    public function studentHasApprovedRequirements(int $studentId): bool { foreach ($this->studentRequirements($studentId) as $req) if (empty($req['file_path']) || ($req['status'] ?? '') !== 'approved') return false; return true; }
    public function studentRequirementFilePaths(int $studentId): array { return array_values(array_filter(array_map(static fn ($req) => $req['file_path'] ?? null, $this->studentRequirements($studentId)))); }

    public function enrollmentCreate(int $studentId, int $companyId, string $startDate, string $endDate, int $requiredHours, string $academicTerm = '', string $termStartDate = '', string $termEndDate = ''): int
    {
        DB::statement('INSERT INTO ojt_enrollments (student_id, company_id, academic_term, term_start_date, term_end_date, start_date, end_date, required_hours, status, predeployment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending", "not_submitted") ON DUPLICATE KEY UPDATE company_id = VALUES(company_id), academic_term = VALUES(academic_term), term_start_date = VALUES(term_start_date), term_end_date = VALUES(term_end_date), start_date = VALUES(start_date), end_date = VALUES(end_date), required_hours = VALUES(required_hours)', [$studentId, $companyId, $academicTerm, $termStartDate ?: null, $termEndDate ?: null, $startDate, $endDate, $requiredHours]);
        return (int)(DB::scalar('SELECT id FROM ojt_enrollments WHERE student_id = ?', [$studentId]) ?? 0);
    }
    public function enrollmentActiveCount(): int { return (int)DB::scalar('SELECT COUNT(*) FROM ojt_enrollments WHERE status = "active"'); }
    public function enrollmentStatusDistribution(): array { return $this->rows(DB::select('SELECT status label, COUNT(*) value FROM ojt_enrollments GROUP BY status ORDER BY status')); }
    public function enrollmentMonthlyTrends(): array { return $this->rows(DB::select('SELECT DATE_FORMAT(created_at, "%Y-%m") label, COUNT(*) value FROM ojt_enrollments GROUP BY DATE_FORMAT(created_at, "%Y-%m") ORDER BY label')); }
    public function enrollmentCompletionRatesByCourse(): array { return $this->rows(DB::select('SELECT s.course AS label, COUNT(e.id) AS total, ROUND(AVG(LEAST(COALESCE(dt.logged_hours, 0) / NULLIF(e.required_hours, 0) * 100, 100)), 2) AS value FROM ojt_enrollments e JOIN students s ON s.id = e.student_id LEFT JOIN (SELECT student_id, SUM(hours) logged_hours FROM daily_time_records GROUP BY student_id) dt ON dt.student_id = e.student_id GROUP BY s.course ORDER BY s.course')); }
    public function enrollmentStudentProgressByCourse(): array
    {
        $rows = $this->rows(DB::select('SELECT s.course, s.student_no, u.name, COALESCE((SELECT SUM(d.hours) FROM daily_time_records d WHERE d.student_id = e.student_id), 0) AS logged_hours, e.required_hours, LEAST(ROUND(COALESCE((SELECT SUM(d.hours) FROM daily_time_records d WHERE d.student_id = e.student_id), 0) / NULLIF(e.required_hours, 0) * 100, 1), 100) AS pct FROM ojt_enrollments e JOIN students s ON s.id = e.student_id JOIN users u ON u.id = s.user_id ORDER BY s.course, pct DESC'));
        $grouped = [];
        foreach ($rows as $row) $grouped[$row['course']][] = ['name' => $row['name'], 'student_no' => $row['student_no'], 'logged' => (float)$row['logged_hours'], 'required' => (int)$row['required_hours'], 'pct' => (float)$row['pct']];
        return $grouped;
    }
    public function enrollmentCountByCoordinator(int $coordinatorUserId, ?string $status = null): int { $sql = 'SELECT COUNT(*) FROM ojt_enrollments e JOIN students s ON s.id = e.student_id WHERE s.coordinator_id = ?'; $params = [$coordinatorUserId]; if ($status) { $sql .= ' AND e.status = ?'; $params[] = $status; } return (int)DB::scalar($sql, $params); }
    public function enrollmentStatusDistributionByCoordinator(int $coordinatorUserId): array { return $this->rows(DB::select('SELECT e.status label, COUNT(*) value FROM ojt_enrollments e JOIN students s ON s.id = e.student_id WHERE s.coordinator_id = ? GROUP BY e.status ORDER BY e.status', [$coordinatorUserId])); }
    public function enrollmentCompletionRatesByCourseByCoordinator(int $coordinatorUserId): array { return $this->rows(DB::select('SELECT s.course AS label, COUNT(e.id) AS total, ROUND(AVG(LEAST(COALESCE(dt.logged_hours, 0) / NULLIF(e.required_hours, 0) * 100, 100)), 2) AS value FROM ojt_enrollments e JOIN students s ON s.id = e.student_id LEFT JOIN (SELECT student_id, SUM(hours) logged_hours FROM daily_time_records GROUP BY student_id) dt ON dt.student_id = e.student_id WHERE s.coordinator_id = ? GROUP BY s.course ORDER BY s.course', [$coordinatorUserId])); }
    public function enrollmentMonthlyTrendsByCoordinator(int $coordinatorUserId): array { return $this->rows(DB::select('SELECT DATE_FORMAT(e.created_at, "%Y-%m") label, COUNT(*) value FROM ojt_enrollments e JOIN students s ON s.id = e.student_id WHERE s.coordinator_id = ? GROUP BY DATE_FORMAT(e.created_at, "%Y-%m") ORDER BY label', [$coordinatorUserId])); }
    public function enrollmentDetailsByStudent(int $studentId): ?array
    {
        $enrollment = $this->row(DB::selectOne('SELECT e.*, pc.name company_name, pc.address company_address, pc.contact_person, pc.contact_email FROM ojt_enrollments e JOIN partner_companies pc ON pc.id = e.company_id WHERE e.student_id = ?', [$studentId]));
        if (!$enrollment) {
            return null;
        }

        $enrollment['predeployment_status'] = $this->effectivePredeploymentStatusForStudent($studentId, $enrollment['predeployment_status'] ?? null);
        return $enrollment;
    }
    public function enrollmentAllowsReports(?array $enrollment): bool
    {
        if (!$enrollment) return false;
        if (($enrollment['status'] ?? '') !== 'active' || ($enrollment['predeployment_status'] ?? '') !== 'orientation_completed') return false;
        $startDate = $enrollment['official_start_date'] ?? $enrollment['start_date'] ?? null;
        if (!$startDate || strtotime((string)$startDate) === false) return false;
        $today = (new \DateTimeImmutable('now', new \DateTimeZone(config('app.timezone', 'Asia/Manila'))))->format('Y-m-d');
        return $today >= date('Y-m-d', strtotime((string)$startDate));
    }
    public function enrollmentReportLockMessage(?array $enrollment): string
    {
        if (!$enrollment) return 'DTR and weekly reports are locked until you are enrolled and deployed to a company.';
        if (($enrollment['predeployment_status'] ?? '') !== 'orientation_completed') return 'DTR and weekly reports are locked until your documents are approved, forwarded, accepted, and the company completes your orientation.';
        if (($enrollment['status'] ?? '') !== 'active') return 'DTR and weekly reports are locked until your OJT deployment becomes active.';
        $startDate = $enrollment['official_start_date'] ?? $enrollment['start_date'] ?? null;
        $today = (new \DateTimeImmutable('now', new \DateTimeZone(config('app.timezone', 'Asia/Manila'))))->format('Y-m-d');
        if ($startDate && strtotime((string)$startDate) !== false && $today < date('Y-m-d', strtotime((string)$startDate))) return 'DTR and weekly reports will unlock on your official OJT start date: ' . date('M d, Y', strtotime((string)$startDate)) . '.';
        return 'DTR and weekly reports are now unlocked.';
    }
    public function studentCanSubmitOjtReports(int $studentId): bool { return $this->enrollmentAllowsReports($this->enrollmentDetailsByStudent($studentId)); }
    public function enrollmentsDeployedByCompany(int $companyId): array
    {
        $enrollments = $this->rows(DB::select('SELECT e.*, s.student_no, s.course, s.year_level, u.name student_name, u.email student_email FROM ojt_enrollments e JOIN students s ON s.id = e.student_id JOIN users u ON u.id = s.user_id WHERE e.company_id = ? ORDER BY CASE e.predeployment_status WHEN "orientation_completed" THEN 1 WHEN "orientation_scheduled" THEN 2 WHEN "accepted" THEN 3 WHEN "forwarded" THEN 4 WHEN "approved" THEN 5 WHEN "submitted" THEN 6 WHEN "needs_revision" THEN 7 WHEN "not_submitted" THEN 8 ELSE 9 END, COALESCE(e.forwarded_at, e.created_at) DESC', [$companyId]));

        foreach ($enrollments as &$enrollment) {
            $enrollment['predeployment_status'] = $this->effectivePredeploymentStatusForStudent((int)$enrollment['student_id'], $enrollment['predeployment_status'] ?? null);
        }
        unset($enrollment);

        return $enrollments;
    }
    public function enrollmentFind(int $id): ?array
    {
        $enrollment = $this->row(DB::selectOne('SELECT e.*, s.student_no, s.course, s.year_level, s.cor_file, u.name student_name, u.email student_email FROM ojt_enrollments e JOIN students s ON s.id = e.student_id JOIN users u ON u.id = s.user_id WHERE e.id = ?', [$id]));
        if (!$enrollment) {
            return null;
        }

        $enrollment['predeployment_status'] = $this->effectivePredeploymentStatusForStudent((int)$enrollment['student_id'], $enrollment['predeployment_status'] ?? null);
        return $enrollment;
    }
    public function enrollmentSetPredeploymentStatus(int $studentId, string $status): void { DB::update('UPDATE ojt_enrollments SET predeployment_status = ? WHERE student_id = ?', [$status, $studentId]); }
    public function enrollmentApproveAndForward(int $enrollmentId, string $endorsementFile): void { DB::update('UPDATE ojt_enrollments SET predeployment_status = "forwarded", endorsement_file = ?, forwarded_at = NOW() WHERE id = ?', [$endorsementFile, $enrollmentId]); }
    public function enrollmentAcceptDeployment(int $enrollmentId): void { DB::update('UPDATE ojt_enrollments SET predeployment_status = "accepted", accepted_at = NOW() WHERE id = ?', [$enrollmentId]); }
    public function enrollmentScheduleOrientation(int $enrollmentId, string $dateTime, string $notes): void { DB::update('UPDATE ojt_enrollments SET predeployment_status = "orientation_scheduled", orientation_datetime = ?, orientation_notes = ? WHERE id = ?', [$dateTime, $notes, $enrollmentId]); }
    public function enrollmentCompleteOrientation(int $enrollmentId, string $officialStart, string $projectedEnd): void { DB::update('UPDATE ojt_enrollments SET predeployment_status = "orientation_completed", status = "active", official_start_date = ?, projected_end_date = ?, start_date = ?, end_date = ? WHERE id = ?', [$officialStart, $projectedEnd, $officialStart, $projectedEnd, $enrollmentId]); }
    public function enrollmentSyncCompletion(int $studentId): void
    {
        $rows = $this->rows(DB::select('SELECT e.id, e.required_hours, COALESCE(SUM(d.hours), 0) rendered_hours FROM ojt_enrollments e LEFT JOIN daily_time_records d ON d.student_id = e.student_id WHERE e.student_id = ? AND e.status = "active" GROUP BY e.id, e.required_hours', [$studentId]));
        foreach ($rows as $row) if ((float)$row['rendered_hours'] >= (float)$row['required_hours']) DB::update('UPDATE ojt_enrollments SET status = "completed" WHERE id = ?', [$row['id']]);
    }

    public function reportAddDtr(int $studentId, string $date, string $timeIn, string $timeOut, string $tasks): void
    {
        $tsIn = strtotime($timeIn); $tsOut = strtotime($timeOut);
        if ($tsIn === false || $tsOut === false) throw new RuntimeException('Invalid time-in or time-out values.');
        if ($tsOut <= $tsIn) $tsOut += 86400;
        DB::table('daily_time_records')->insert(['student_id' => $studentId, 'work_date' => $date, 'time_in' => $timeIn, 'time_out' => $timeOut, 'hours' => ($tsOut - $tsIn) / 3600, 'tasks_done' => $tasks]);
    }
    public function reportDtrByStudent(int $studentId): array { return $this->rows(DB::select('SELECT * FROM daily_time_records WHERE student_id = ? ORDER BY work_date DESC', [$studentId])); }
    public function reportTotalHours(int $studentId): float { return (float)DB::scalar('SELECT COALESCE(SUM(hours),0) FROM daily_time_records WHERE student_id = ?', [$studentId]); }
    public function reportAddWeekly(int $studentId, int $weekNo, ?string $text, ?string $filePath): void { DB::table('weekly_reports')->insert(['student_id' => $studentId, 'week_no' => $weekNo, 'report_text' => $text, 'file_path' => $filePath]); }
    public function reportWeeklyByStudent(int $studentId): array { return $this->rows(DB::select('SELECT * FROM weekly_reports WHERE student_id = ? ORDER BY week_no DESC', [$studentId])); }

    public function evaluationSubmit(int $enrollmentId, int $companyId, int $rating, string $comments): void { DB::statement('INSERT INTO evaluations (enrollment_id, company_id, rating, comments) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), comments = VALUES(comments), submitted_at = CURRENT_TIMESTAMP', [$enrollmentId, $companyId, $rating, $comments]); }
    public function evaluationByEnrollment(int $enrollmentId): ?array { return $this->row(DB::selectOne('SELECT * FROM evaluations WHERE enrollment_id = ?', [$enrollmentId])); }
    public function evaluationsAllWithDetails(): array { return $this->rows(DB::select('SELECT e.*, u.name AS student_name, s.student_no, s.course, s.year_level, c.name AS company_name, en.start_date, en.end_date FROM evaluations e JOIN ojt_enrollments en ON en.id = e.enrollment_id JOIN students s ON s.id = en.student_id JOIN users u ON u.id = s.user_id JOIN partner_companies c ON c.id = e.company_id ORDER BY e.submitted_at DESC')); }
    public function evaluationsByCoordinator(int $coordinatorId): array { return $this->rows(DB::select('SELECT e.*, u.name AS student_name, s.student_no, s.course, s.year_level, c.name AS company_name, en.start_date, en.end_date FROM evaluations e JOIN ojt_enrollments en ON en.id = e.enrollment_id JOIN students s ON s.id = en.student_id JOIN users u ON u.id = s.user_id JOIN partner_companies c ON c.id = e.company_id WHERE s.coordinator_id = ? ORDER BY e.submitted_at DESC', [$coordinatorId])); }

    public function notificationCreate(int $userId, string $title, string $message, string $link = ''): void { DB::table('notifications')->insert(['user_id' => $userId, 'title' => $title, 'message' => $message, 'link' => $link]); }
    public function notificationsRecentForUser(int $userId, int $limit = 8): array { return $this->rows(DB::select('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . max(1, $limit), [$userId])); }
    public function notificationsUnreadCount(int $userId): int { return (int)DB::scalar('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]); }
    public function notificationsMarkAllRead(int $userId): void { DB::table('notifications')->where('user_id', $userId)->where('is_read', 0)->update(['is_read' => 1]); }

    public function emailSend(string $recipient, string $subject, string $type, string $template, array $data, array $attachments = []): bool
    {
        $status = 'failed'; $error = null;
        try {
            $body = View::make('emails.' . $template, $data)->render();
            Mail::html($body, function ($message) use ($recipient, $subject, $attachments) {
                $message->from(config('mail.from.address'), config('mail.from.name'));
                $message->replyTo(config('mail.from.address'), config('mail.from.name'));
                $message->to($recipient)->subject($subject);
                foreach ($attachments as $attachment) {
                    $path = is_array($attachment) ? ($attachment['path'] ?? '') : (string)$attachment;
                    $name = is_array($attachment) ? ($attachment['name'] ?? '') : '';
                    $fullPath = public_path(ltrim($path, '/\\'));
                    if ($path && is_file($fullPath)) $message->attach($fullPath, $name ? ['as' => $name] : []);
                }
            });
            $status = 'sent'; return true;
        } catch (Throwable $e) {
            $error = $e->getMessage(); return false;
        } finally {
            DB::table('email_logs')->insert(['recipient_email' => $recipient, 'subject' => $subject, 'type' => $type, 'sent_at' => now(), 'status' => $status, 'error_message' => $error]);
        }
    }
    public function emailLogsFiltered(array $filters = []): array
    {
        $sql = 'SELECT * FROM email_logs WHERE 1=1'; $params = [];
        if (!empty($filters['type'])) { $sql .= ' AND type = ?'; $params[] = $filters['type']; }
        if (!empty($filters['status'])) { $sql .= ' AND status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['date_from'])) { $sql .= ' AND DATE(sent_at) >= ?'; $params[] = $filters['date_from']; }
        if (!empty($filters['date_to'])) { $sql .= ' AND DATE(sent_at) <= ?'; $params[] = $filters['date_to']; }
        return $this->rows(DB::select($sql . ' ORDER BY sent_at DESC LIMIT 300', $params));
    }
    public function emailLogTypes(): array { return array_map(static fn ($row) => (string)$row->type, DB::select('SELECT DISTINCT type FROM email_logs ORDER BY type')); }

    public function formatPhilippineMobile(string $contactNumber): string
    {
        $digits = preg_replace('/\D+/', '', $contactNumber);
        if (str_starts_with($digits, '63')) $digits = substr($digits, 2);
        if (str_starts_with($digits, '0')) $digits = substr($digits, 1);
        if (!preg_match('/^9\d{9}$/', $digits)) throw new RuntimeException('Contact number must be a valid Philippine mobile number.');
        return '+63 ' . substr($digits, 0, 3) . ' ' . substr($digits, 3, 3) . ' ' . substr($digits, 6, 4);
    }
}
