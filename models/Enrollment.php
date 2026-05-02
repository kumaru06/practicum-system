<?php
class Enrollment
{
    public function __construct(private PDO $db) {}

    public function create(int $studentId, int $companyId, string $startDate, string $endDate, int $requiredHours): int
    {
        $stmt = $this->db->prepare('INSERT INTO ojt_enrollments (student_id, company_id, start_date, end_date, required_hours, status) VALUES (?, ?, ?, ?, ?, "active") ON DUPLICATE KEY UPDATE company_id = VALUES(company_id), start_date = VALUES(start_date), end_date = VALUES(end_date), required_hours = VALUES(required_hours), status = "active"');
        $stmt->execute([$studentId, $companyId, $startDate, $endDate, $requiredHours]);
        return (int)$this->db->lastInsertId();
    }

    public function activeCount(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM ojt_enrollments WHERE status = "active"')->fetchColumn();
    }

    public function syncCompletion(int $studentId): void
    {
        $stmt = $this->db->prepare('SELECT e.id, e.required_hours, COALESCE(SUM(d.hours), 0) rendered_hours FROM ojt_enrollments e LEFT JOIN daily_time_records d ON d.student_id = e.student_id WHERE e.student_id = ? AND e.status = "active" GROUP BY e.id, e.required_hours');
        $stmt->execute([$studentId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if ((float)$row['rendered_hours'] >= (float)$row['required_hours']) {
                $update = $this->db->prepare('UPDATE ojt_enrollments SET status = "completed" WHERE id = ?');
                $update->execute([$row['id']]);
            }
        }
    }

    public function statusDistribution(): array
    {
        return $this->db->query('SELECT status label, COUNT(*) value FROM ojt_enrollments GROUP BY status ORDER BY status')->fetchAll();
    }

    public function completionRatesByCourse(): array
    {
        return $this->db->query('
            SELECT
                s.course AS label,
                COUNT(e.id) AS total,
                ROUND(
                    AVG(
                        LEAST(
                            COALESCE(
                                (SELECT SUM(d.hours) FROM daily_time_records d WHERE d.student_id = e.student_id),
                                0
                            ) / NULLIF(e.required_hours, 0) * 100,
                            100
                        )
                    ), 2
                ) AS value
            FROM ojt_enrollments e
            JOIN students s ON s.id = e.student_id
            GROUP BY s.course
            ORDER BY s.course
        ')->fetchAll();
    }

    public function studentProgressByCourse(): array
    {
        $rows = $this->db->query('
            SELECT s.course, s.student_no, u.name,
                COALESCE((SELECT SUM(d.hours) FROM daily_time_records d WHERE d.student_id = e.student_id), 0) AS logged_hours,
                e.required_hours,
                LEAST(ROUND(
                    COALESCE((SELECT SUM(d.hours) FROM daily_time_records d WHERE d.student_id = e.student_id), 0)
                    / NULLIF(e.required_hours, 0) * 100, 1
                ), 100) AS pct
            FROM ojt_enrollments e
            JOIN students s ON s.id = e.student_id
            JOIN users u ON u.id = s.user_id
            ORDER BY s.course, pct DESC
        ')->fetchAll();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['course']][] = [
                'name'       => $row['name'],
                'student_no' => $row['student_no'],
                'logged'     => (float)$row['logged_hours'],
                'required'   => (int)$row['required_hours'],
                'pct'        => (float)$row['pct'],
            ];
        }
        return $grouped;
    }

    public function monthlyEnrollmentTrends(): array
    {
        return $this->db->query('SELECT DATE_FORMAT(created_at, "%Y-%m") label, COUNT(*) value FROM ojt_enrollments GROUP BY DATE_FORMAT(created_at, "%Y-%m") ORDER BY label')->fetchAll();
    }

    public function countByCoordinator(int $coordinatorUserId, ?string $status = null): int
    {
        $sql = 'SELECT COUNT(*) FROM ojt_enrollments e JOIN students s ON s.id = e.student_id WHERE s.coordinator_id = ?';
        $params = [$coordinatorUserId];
        if ($status) {
            $sql .= ' AND e.status = ?';
            $params[] = $status;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function detailsByStudent(int $studentId): ?array
    {
        $stmt = $this->db->prepare('SELECT e.*, pc.name company_name, pc.address company_address, pc.contact_person, pc.contact_email FROM ojt_enrollments e JOIN partner_companies pc ON pc.id = e.company_id WHERE e.student_id = ?');
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: null;
    }

    public function deployedByCompany(int $companyId): array
    {
        $stmt = $this->db->prepare('SELECT e.*, s.student_no, s.course, s.year_level, u.name student_name, u.email student_email FROM ojt_enrollments e JOIN students s ON s.id = e.student_id JOIN users u ON u.id = s.user_id WHERE e.company_id = ? ORDER BY e.start_date DESC');
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT e.*, s.student_no, s.course, s.year_level, u.name student_name, u.email student_email FROM ojt_enrollments e JOIN students s ON s.id = e.student_id JOIN users u ON u.id = s.user_id WHERE e.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
