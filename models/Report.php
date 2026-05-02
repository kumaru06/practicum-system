<?php
class Report
{
    public function __construct(private PDO $db) {}

    public function addDtr(int $studentId, string $date, string $timeIn, string $timeOut, string $tasks): void
    {
        $tsIn  = strtotime($timeIn);
        $tsOut = strtotime($timeOut);
        if ($tsIn === false || $tsOut === false) {
            throw new RuntimeException('Invalid time-in or time-out values.');
        }
        // Handle overnight shifts (e.g. time-in 23:00, time-out 01:00)
        if ($tsOut <= $tsIn) {
            $tsOut += 86400;
        }
        $hours = ($tsOut - $tsIn) / 3600;
        $stmt = $this->db->prepare('INSERT INTO daily_time_records (student_id, work_date, time_in, time_out, hours, tasks_done) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$studentId, $date, $timeIn, $timeOut, $hours, $tasks]);
    }

    public function dtrByStudent(int $studentId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM daily_time_records WHERE student_id = ? ORDER BY work_date DESC');
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function totalHours(int $studentId): float
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(hours),0) FROM daily_time_records WHERE student_id = ?');
        $stmt->execute([$studentId]);
        return (float)$stmt->fetchColumn();
    }

    public function addWeekly(int $studentId, int $weekNo, ?string $text, ?string $filePath): void
    {
        $stmt = $this->db->prepare('INSERT INTO weekly_reports (student_id, week_no, report_text, file_path) VALUES (?, ?, ?, ?)');
        $stmt->execute([$studentId, $weekNo, $text, $filePath]);
    }

    public function weeklyByStudent(int $studentId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM weekly_reports WHERE student_id = ? ORDER BY week_no DESC');
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }
}
