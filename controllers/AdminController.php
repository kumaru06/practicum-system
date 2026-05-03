<?php
class AdminController extends BaseController
{
    public function dashboard(): void
    {
        require_role('admin');
        $users = new User($this->db);
        $company = new Company($this->db);
        $enroll = new Enrollment($this->db);
        $this->render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => [
                'coordinators' => $users->countRole('coordinator'),
                'companies' => $company->count(),
                'students' => $users->countRole('student'),
                'active' => $enroll->activeCount(),
            ],
            'companies' => $company->all(),
            'charts' => [
                'statusDistribution' => $enroll->statusDistribution(),
                'completionRates' => $enroll->completionRatesByCourse(),
                'monthlyTrends' => $enroll->monthlyEnrollmentTrends(),
                'courseStudents' => $enroll->studentProgressByCourse(),
            ],
        ]);
    }

    public function manageCoordinators(): void
    {
        require_role('admin');
        $this->render('admin/coordinators', [
            'title' => 'Coordinators',
            'coordinators' => (new User($this->db))->byRole('coordinator'),
        ]);
    }

    public function managePartners(): void
    {
        require_role('admin');
        $this->render('admin/partners', [
            'title' => 'Partner Companies',
            'partners' => (new Company($this->db))->all(),
            'programs' => (new Program($this->db))->all(true),
        ]);
    }

    public function managePrograms(): void
    {
        require_role('admin');
        $this->render('admin/programs', [
            'title' => 'Programs / Courses',
            'programs' => (new Program($this->db))->all(),
        ]);
    }

    public function manageUsers(): void
    {
        require_role('admin');
        $this->render('admin/users', [
            'title' => 'Students',
            'allUsers' => (new User($this->db))->allStudents(),
        ]);
    }

    public function evaluations(): void
    {
        require_role('admin');
        $this->render('admin/evaluations', [
            'title' => 'Evaluations',
            'evaluations' => (new Evaluation($this->db))->allWithDetails(),
        ]);
    }

    public function emailLogs(): void
    {
        require_role('admin');
        $filters = [
            'type' => trim($_GET['type'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
        ];
        $this->render('admin/email_logs', [
            'title' => 'Email Logs',
            'logs' => (new Email($this->db))->filtered($filters),
            'filters' => $filters,
        ]);
    }

    public function createCoordinator(): void
    {
        require_role('admin');
        $p = $this->post();
        try {
            $password = random_password();
            $userId = (new User($this->db))->create(trim($p['name']), trim($p['email']), $password, 'coordinator', current_user()['id'], 0);
            $stmt = $this->db->prepare('INSERT INTO coordinators (user_id, department) VALUES (?, ?)');
            $stmt->execute([$userId, trim($p['department'] ?? 'OJT Department') ?: 'OJT Department']);
            (new Email($this->db))->send(trim($p['email']), 'Your AMA Practicum Coordinator Account', 'account_credentials', 'account_credentials', [
                'name' => trim($p['name']),
                'email' => trim($p['email']),
                'password' => $password,
                'roleLabel' => 'OJT Coordinator',
            ]);
            flash('success', 'Coordinator account created and credentials email was processed.');
        } catch (Throwable $e) {
            $msg = str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate entry')
                ? 'Email already exists.'
                : $e->getMessage();
            flash('error', $msg);
        }
        redirect('index.php?r=admin_coordinators');
    }

    public function createCompany(): void
    {
        require_role('admin');
        $p = $this->post();
        try {
            $password = random_password();
            $programIds = $p['program_ids'] ?? [];
            if (!$programIds) {
                throw new RuntimeException('Select at least one accepted program/course.');
            }
            $userId = (new User($this->db))->create(trim($p['company_name']), trim($p['contact_email']), $password, 'partner', current_user()['id'], 0);
            (new Company($this->db))->create($userId, trim($p['company_name']), trim($p['address'] ?? ''), trim($p['contact_person']), trim($p['contact_email']), trim($p['contact_number'] ?? ''), $programIds);
            (new Email($this->db))->send(trim($p['contact_email']), 'Your AMA Practicum Partner Account', 'account_credentials', 'account_credentials', [
                'name' => trim($p['contact_person']),
                'email' => trim($p['contact_email']),
                'password' => $password,
                'roleLabel' => 'Industry Partner',
            ]);
            flash('success', 'Partner company account created and credentials email was processed.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('index.php?r=admin_partners');
    }

    public function saveProgram(): void
    {
        require_role('admin');
        $p = $this->post();
        try {
            $programs = new Program($this->db);
            if (!empty($p['program_id'])) {
                $programs->update((int)$p['program_id'], trim($p['code']), trim($p['name']), (int)$p['required_hours'], (int)($p['is_active'] ?? 1));
                flash('success', 'Program updated.');
            } else {
                $programs->create(trim($p['code']), trim($p['name']), (int)$p['required_hours']);
                flash('success', 'Program created.');
            }
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('index.php?r=admin_programs');
    }

    public function deleteProgram(): void
    {
        require_role('admin');
        $p = $this->post();
        try {
            (new Program($this->db))->delete((int)$p['program_id']);
            flash('success', 'Program deleted.');
        } catch (Throwable $e) {
            flash('error', 'Program is already in use. Deactivate it instead.');
        }
        redirect('index.php?r=admin_programs');
    }

    public function toggleUser(): void
    {
        require_role('admin');
        $p = $this->post();
        (new User($this->db))->setActive((int)$p['user_id'], (int)$p['active']);
        flash('success', 'User status updated.');
        $back = $p['redirect'] ?? 'admin_users';
        redirect('index.php?r=' . $back);
    }
}
