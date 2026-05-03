<?php
class CoordinatorController extends BaseController
{
    public function dashboard(): void
    {
        require_role('coordinator');
        $students = new Student($this->db);
        $enroll = new Enrollment($this->db);
        $coordId = current_user()['id'];
        $this->render('coordinator/dashboard', [
            'title' => 'Coordinator Dashboard',
            'stats' => [
                'students'  => $students->countByCoordinator($coordId),
                'enrolled'  => $enroll->countByCoordinator($coordId, 'active'),
                'completed' => $enroll->countByCoordinator($coordId, 'completed'),
                'pending'   => $enroll->countByCoordinator($coordId, 'pending'),
            ],
            'charts' => [
                'statusDistribution' => $enroll->statusDistributionByCoordinator($coordId),
                'completionRates'    => $enroll->completionRatesByCourseByCoordinator($coordId),
                'monthlyTrends'      => $enroll->monthlyEnrollmentTrendsByCoordinator($coordId),
            ],
        ]);
    }

    public function manage(): void
    {
        require_role('coordinator');
        $coordId = current_user()['id'];
        $this->render('coordinator/manage', [
            'title' => 'Coordinator',
            'students'  => (new Student($this->db))->allByCoordinator($coordId),
            'companies' => (new Company($this->db))->all(),
            'programs'  => (new Program($this->db))->all(true),
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
            $program = (new Program($this->db))->find((int)$p['program_id']);
            if (!$program) {
                throw new RuntimeException('Select a valid program/course.');
            }
            $userId = (new User($this->db))->create(trim($p['full_name']), trim($p['email']), $password, 'student', current_user()['id'], 0);
            (new Student($this->db))->create($userId, trim($p['student_no']), $program['name'], trim($p['year_level']), $corPath, current_user()['id'], (int)$program['id'], trim($p['section'] ?? ''));
            flash('success', 'Student account created. Credentials will be emailed when the student is enrolled.');
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
            (new Enrollment($this->db))->create($studentId, $companyId, $p['start_date'], $p['end_date'], (int)$p['required_hours'], trim($p['academic_term'] ?? ''), $p['term_start_date'] ?? '', $p['term_end_date'] ?? '');
            $company = (new Company($this->db))->find($companyId);
            $tempPassword = random_password();
            (new User($this->db))->updatePassword((int)$student['user_id'], $tempPassword, 0);
            $email = new Email($this->db);
            $email->send($student['email'], 'You are now enrolled in OJT – AMA Computer College', 'student_enrollment', 'student_enrollment', [
                'student' => $student,
                'company' => $company,
                'startDate' => $p['start_date'],
                'endDate' => $p['end_date'],
                'academicTerm' => trim($p['academic_term'] ?? ''),
                'termStartDate' => $p['term_start_date'] ?? '',
                'termEndDate' => $p['term_end_date'] ?? '',
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

    public function forwardDeployment(): void
    {
        require_role('coordinator');
        $p = $this->post();
        try {
            $enrollment = (new Enrollment($this->db))->find((int)$p['enrollment_id']);
            if (!$enrollment) {
                throw new RuntimeException('Enrollment not found.');
            }
            $student = (new Student($this->db))->find((int)$enrollment['student_id']);
            if (!$student || (int)$student['coordinator_id'] !== (int)current_user()['id']) {
                throw new RuntimeException('Student does not belong to your coordination.');
            }
            $endorsement = upload_document($_FILES['endorsement_file'] ?? [], 'endorsements');
            (new Enrollment($this->db))->approveAndForward((int)$enrollment['id'], $endorsement);
            $company = (new Company($this->db))->find((int)$enrollment['company_id']);
            if ($company) {
                (new Email($this->db))->send($company['contact_email'], 'Student Deployment Documents Forwarded', 'deployment_forwarded', 'company_deployment', [
                    'student' => $student,
                    'company' => $company,
                    'startDate' => $enrollment['start_date'],
                    'endDate' => $enrollment['end_date'],
                    'requiredHours' => (int)$enrollment['required_hours'],
                    'coordinator' => current_user(),
                ]);
            }
            flash('success', 'Documents approved and forwarded to partner company.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('index.php?r=coordinator_students');
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
