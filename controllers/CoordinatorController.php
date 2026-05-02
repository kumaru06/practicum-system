<?php
class CoordinatorController extends BaseController
{
    public function dashboard(): void
    {
        require_role('coordinator');
        $students = new Student($this->db);
        $enroll = new Enrollment($this->db);
        $this->render('coordinator/dashboard', [
            'title' => 'Coordinator Dashboard',
            'stats' => [
                'students' => $students->countByCoordinator(current_user()['id']),
                'enrolled' => $enroll->countByCoordinator(current_user()['id'], 'active'),
                'completed' => $enroll->countByCoordinator(current_user()['id'], 'completed'),
            ],
            'students' => $students->allByCoordinator(current_user()['id']),
            'companies' => (new Company($this->db))->all(),
        ]);
    }

    public function myStudents(): void
    {
        require_role('coordinator');
        $this->render('coordinator/my_students', [
            'title' => 'My Students',
            'students' => (new Student($this->db))->allByCoordinator(current_user()['id']),
            'evaluations' => (new Evaluation($this->db))->byCoordinator(current_user()['id']),
        ]);
    }

    public function evaluations(): void
    {
        require_role('coordinator');
        $this->render('coordinator/evaluations', [
            'title' => 'Evaluations',
            'evaluations' => (new Evaluation($this->db))->byCoordinator(current_user()['id']),
        ]);
    }

    public function createStudent(): void
    {
        require_role('coordinator');
        $p = $this->post();
        try {
            $password = random_password();
            $corPath = upload_cor($_FILES['cor_file'] ?? []);
            $userId = (new User($this->db))->create(trim($p['full_name']), trim($p['email']), $password, 'student', current_user()['id'], 0);
            (new Student($this->db))->create($userId, trim($p['student_no']), trim($p['course']), trim($p['year_level']), $corPath, current_user()['id']);
            flash('success', 'Student account created. A fresh temporary password will be emailed when the student is enrolled.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('index.php?r=coordinator');
    }

    public function enrollStudent(): void
    {
        require_role('coordinator');
        $p = $this->post();
        try {
            $studentId = (int)$p['student_id'];
            $companyId = (int)$p['company_id'];
            $student = (new Student($this->db))->find($studentId);
            if (!$student || (int)$student['coordinator_id'] !== current_user()['id']) {
                throw new RuntimeException('Student does not belong to your coordination.');
            }
            (new Enrollment($this->db))->create($studentId, $companyId, $p['start_date'], $p['end_date'], (int)$p['required_hours']);
            $company = (new Company($this->db))->find($companyId);
            $tempPassword = random_password();
            (new User($this->db))->updatePassword((int)$student['user_id'], $tempPassword, 0);
            $email = new Email($this->db);
            $email->send($student['email'], 'You are now enrolled in OJT – AMA Computer College', 'student_enrollment', 'student_enrollment', [
                'student' => $student,
                'company' => $company,
                'startDate' => $p['start_date'],
                'endDate' => $p['end_date'],
                'requiredHours' => (int)$p['required_hours'],
                'password' => $tempPassword,
                'coordinator' => current_user(),
            ]);
            $email->send($company['contact_email'], 'OJT Student Deployment Notice – AMA Computer College', 'company_deployment', 'company_deployment', [
                'student' => $student,
                'company' => $company,
                'startDate' => $p['start_date'],
                'endDate' => $p['end_date'],
                'requiredHours' => (int)$p['required_hours'],
                'coordinator' => current_user(),
            ]);
            flash('success', 'Student enrolled and deployment emails were processed. Check email logs for status.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('index.php?r=coordinator');
    }

    public function resetStudentPassword(): void
    {
        require_role('coordinator');
        $p = $this->post();
        $student = (new Student($this->db))->find((int)$p['student_id']);
        if (!$student || (int)$student['coordinator_id'] !== current_user()['id']) {
            flash('error', 'Invalid student.');
            redirect('index.php?r=coordinator_students');
        }
        $password = random_password();
        (new User($this->db))->updatePassword((int)$student['user_id'], $password, 0);
        (new Email($this->db))->send($student['email'], 'Your AMA OJT password has been reset', 'password_reset', 'student_enrollment', [
            'student' => $student,
            'company' => ['name' => 'Current OJT Company'],
            'startDate' => '',
            'endDate' => '',
            'requiredHours' => '',
            'password' => $password,
            'coordinator' => current_user(),
        ]);
        flash('success', 'Student password reset and emailed.');
        redirect('index.php?r=coordinator_students');
    }
}
