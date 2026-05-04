<?php
class Student
{
    public function __construct(private PDO $db) {}

    public function create(int $userId, string $studentNo, string $course, string $yearLevel, string $corFile, int $coordinatorId, ?int $programId = null, string $section = ''): int
    {
        $stmt = $this->db->prepare('INSERT INTO students (user_id, student_no, program_id, course, year_level, section, cor_file, coordinator_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $studentNo, $programId, $course, $yearLevel, $section, $corFile, $coordinatorId]);
        return (int)$this->db->lastInsertId();
    }

    public function allByCoordinator(int $coordinatorUserId): array
    {
        $stmt = $this->db->prepare('SELECT s.*, u.name, u.email, u.is_active, p.code program_code, p.required_hours program_required_hours, e.id enrollment_id, e.status deployment_status, e.predeployment_status, e.required_hours, COALESCE(SUM(d.hours), 0) rendered_hours, pc.name company_name FROM students s JOIN users u ON u.id = s.user_id LEFT JOIN programs p ON p.id = s.program_id LEFT JOIN ojt_enrollments e ON e.student_id = s.id LEFT JOIN daily_time_records d ON d.student_id = s.id LEFT JOIN partner_companies pc ON pc.id = e.company_id WHERE s.coordinator_id = ? GROUP BY s.id, u.id, p.id, e.id, pc.id ORDER BY u.name');
        $stmt->execute([$coordinatorUserId]);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query('SELECT s.*, u.name, u.email, c.name coordinator_name FROM students s JOIN users u ON u.id = s.user_id LEFT JOIN users c ON c.id = s.coordinator_id ORDER BY u.name')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT s.*, u.name, u.email, u.is_active, c.name coordinator_name, c.email coordinator_email FROM students s JOIN users u ON u.id = s.user_id LEFT JOIN users c ON c.id = s.coordinator_id WHERE s.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT s.*, u.name, u.email, c.name coordinator_name, c.email coordinator_email FROM students s JOIN users u ON u.id = s.user_id LEFT JOIN users c ON c.id = s.coordinator_id WHERE s.user_id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function countByCoordinator(int $coordinatorUserId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM students WHERE coordinator_id = ?');
        $stmt->execute([$coordinatorUserId]);
        return (int)$stmt->fetchColumn();
    }

    public function updateProfile(int $studentId, array $data, ?string $photoFile): void
    {
        $stmt = $this->db->prepare('UPDATE students SET address = ?, contact_number = ?, emergency_contact_name = ?, emergency_contact_number = ?, guardian_name = ?, guardian_contact = ?, year_level = ?, section = ?, photo_file = COALESCE(?, photo_file), profile_completed = 1 WHERE id = ?');
        $stmt->execute([
            trim($data['address'] ?? ''),
            trim($data['contact_number'] ?? ''),
            trim($data['emergency_contact_name'] ?? ''),
            trim($data['emergency_contact_number'] ?? ''),
            trim($data['guardian_name'] ?? ''),
            trim($data['guardian_contact'] ?? ''),
            trim($data['year_level'] ?? ''),
            trim($data['section'] ?? ''),
            $photoFile,
            $studentId,
        ]);
    }

    public function requirementDefinitions(): array
    {
        return [
            'guardian_consent' => ['name' => 'Parent/Guardian Consent Form', 'notes' => 'Download the template, fill it out, have it signed and notarized, then upload the scanned copy.'],
            'philhealth' => ['name' => 'PhilHealth Card / Document', 'notes' => 'Upload scan or photo.'],
            'vaccine_card' => ['name' => 'Vaccine Card', 'notes' => 'Upload scan or photo.'],
            'guardian_id' => ['name' => "Guardian's Valid ID", 'notes' => 'Upload scan or photo.'],
            'cor' => ['name' => 'Certificate of Registration (COR)', 'notes' => 'Upload current term COR.'],
        ];
    }

    public function requirements(int $studentId): array
    {
        $defs = $this->requirementDefinitions();
        $stmt = $this->db->prepare('SELECT * FROM student_requirements WHERE student_id = ?');
        $stmt->execute([$studentId]);
        $rows = [];
        foreach ($stmt->fetchAll() as $row) {
            $rows[$row['requirement_key']] = $row;
        }
        foreach ($defs as $key => $def) {
            if (!isset($rows[$key])) {
                $rows[$key] = ['requirement_key' => $key, 'requirement_name' => $def['name'], 'notes' => $def['notes'], 'file_path' => null, 'status' => 'pending'];
            } else {
                $rows[$key]['review_notes'] = $rows[$key]['notes'] ?? '';
                $rows[$key]['notes'] = $def['notes'];
            }
        }
        return $rows;
    }

    public function saveRequirement(int $studentId, string $key, string $filePath): void
    {
        $defs = $this->requirementDefinitions();
        if (!isset($defs[$key])) {
            throw new RuntimeException('Invalid requirement.');
        }
        $stmt = $this->db->prepare('INSERT INTO student_requirements (student_id, requirement_key, requirement_name, file_path, status, uploaded_at) VALUES (?, ?, ?, ?, "uploaded", NOW()) ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), status = "uploaded", uploaded_at = NOW()');
        $stmt->execute([$studentId, $key, $defs[$key]['name'], $filePath]);
    }

    public function reviewRequirement(int $studentId, string $key, string $status, string $notes = ''): void
    {
        if (!in_array($status, ['approved', 'rejected'], true)) {
            throw new RuntimeException('Invalid review status.');
        }
        $defs = $this->requirementDefinitions();
        if (!isset($defs[$key])) {
            throw new RuntimeException('Invalid requirement.');
        }
        $stmt = $this->db->prepare('UPDATE student_requirements SET status = ?, notes = ?, reviewed_at = NOW() WHERE student_id = ? AND requirement_key = ? AND file_path IS NOT NULL');
        $stmt->execute([$status, $notes, $studentId, $key]);
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Requirement file is not available for review.');
        }
    }

    public function hasCompleteRequirements(int $studentId): bool
    {
        foreach ($this->requirements($studentId) as $req) {
            if (empty($req['file_path'])) {
                return false;
            }
        }
        return true;
    }

    public function hasApprovedRequirements(int $studentId): bool
    {
        foreach ($this->requirements($studentId) as $req) {
            if (empty($req['file_path']) || ($req['status'] ?? '') !== 'approved') {
                return false;
            }
        }
        return true;
    }

    public function requirementFilePaths(int $studentId): array
    {
        return array_values(array_filter(array_map(static fn ($req) => $req['file_path'] ?? null, $this->requirements($studentId))));
    }
}
