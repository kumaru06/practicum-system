<?php
class Student
{
    public function __construct(private PDO $db) {}

    public function create(int $userId, string $studentNo, string $course, string $yearLevel, string $corFile, int $coordinatorId): int
    {
        $stmt = $this->db->prepare('INSERT INTO students (user_id, student_no, course, year_level, cor_file, coordinator_id) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $studentNo, $course, $yearLevel, $corFile, $coordinatorId]);
        return (int)$this->db->lastInsertId();
    }

    public function allByCoordinator(int $coordinatorUserId): array
    {
        $stmt = $this->db->prepare('SELECT s.*, u.name, u.email, u.is_active, e.status deployment_status, e.required_hours, COALESCE(SUM(d.hours), 0) rendered_hours, pc.name company_name FROM students s JOIN users u ON u.id = s.user_id LEFT JOIN ojt_enrollments e ON e.student_id = s.id LEFT JOIN daily_time_records d ON d.student_id = s.id LEFT JOIN partner_companies pc ON pc.id = e.company_id WHERE s.coordinator_id = ? GROUP BY s.id, u.id, e.id, pc.id ORDER BY u.name');
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
}
