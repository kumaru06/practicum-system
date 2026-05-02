<?php
class StudentController extends BaseController
{
    public function changePasswordForm(): void
    {
        require_role('student');
        $this->render('student/change_password', [
            'title' => 'Change Temporary Password',
        ]);
    }

    public function changePassword(): void
    {
        require_role('student');
        $p = $this->post();
        $password = (string)($p['password'] ?? '');
        $confirm = (string)($p['confirm_password'] ?? '');
        if (strlen($password) < 8) {
            flash('error', 'Password must be at least 8 characters.');
            redirect('index.php');
        }
        if ($password !== $confirm) {
            flash('error', 'Passwords do not match.');
            redirect('index.php');
        }
        (new User($this->db))->updatePassword((int)current_user()['id'], $password, 1);
        $_SESSION['user']['password_changed'] = 1;
        flash('success', 'Password changed successfully. You can now access your student portal.');
        redirect('index.php?r=student');
    }

    public function dashboard(): void
    {
        require_role('student');
        $student = (new Student($this->db))->findByUser(current_user()['id']);
        $enrollment = $student ? (new Enrollment($this->db))->detailsByStudent((int)$student['id']) : null;
        $reports = new Report($this->db);
        $hours = $student ? $reports->totalHours((int)$student['id']) : 0;
        $this->render('student/dashboard', [
            'title' => 'Student Dashboard',
            'student' => $student,
            'enrollment' => $enrollment,
            'dtrs' => $student ? $reports->dtrByStudent((int)$student['id']) : [],
            'weeklyReports' => $student ? $reports->weeklyByStudent((int)$student['id']) : [],
            'hours' => $hours,
        ]);
    }

    public function addDtr(): void
    {
        require_role('student');
        $p = $this->post();
        $student = (new Student($this->db))->findByUser(current_user()['id']);
        if (!$student) {
            flash('error', 'Student record not found.');
            redirect('index.php?r=student');
        }
        (new Report($this->db))->addDtr((int)$student['id'], $p['work_date'], $p['time_in'], $p['time_out'], trim($p['tasks_done']));
        (new Enrollment($this->db))->syncCompletion((int)$student['id']);
        flash('success', 'Daily time record submitted.');
        redirect('index.php?r=student');
    }

    public function addWeekly(): void
    {
        require_role('student');
        $p = $this->post();
        $student = (new Student($this->db))->findByUser(current_user()['id']);
        if (!$student) {
            flash('error', 'Student record not found.');
            redirect('index.php?r=student');
        }
        $path = null;
        if (!empty($_FILES['report_file']['name'])) {
            $path = $this->uploadReport($_FILES['report_file']);
        }
        (new Report($this->db))->addWeekly((int)$student['id'], (int)$p['week_no'], trim($p['report_text'] ?? ''), $path);
        flash('success', 'Weekly report submitted.');
        redirect('index.php?r=student');
    }

    private function uploadReport(array $file): string
    {
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Report file must not exceed 5MB.');
        }
        $allowed = ['application/pdf' => 'pdf'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Weekly report upload must be PDF.');
        }
        $dir = __DIR__ . '/../uploads/reports';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = bin2hex(random_bytes(16)) . '.pdf';
        move_uploaded_file($file['tmp_name'], $dir . '/' . $name);
        return 'uploads/reports/' . $name;
    }
}
