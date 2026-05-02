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
            $userId = (new User($this->db))->create(trim($p['name']), trim($p['email']), $p['password'], 'coordinator', current_user()['id']);
            $stmt = $this->db->prepare('INSERT INTO coordinators (user_id, department) VALUES (?, ?)');
            $stmt->execute([$userId, 'OJT Department']);
            flash('success', 'Coordinator account created.');
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('index.php?r=admin_coordinators');
    }

    public function createCompany(): void
    {
        require_role('admin');
        $p = $this->post();
        try {
            $password = $p['password'] ?: random_password();
            $userId = (new User($this->db))->create(trim($p['company_name']), trim($p['contact_email']), $password, 'partner', current_user()['id']);
            (new Company($this->db))->create($userId, trim($p['company_name']), trim($p['address']), trim($p['contact_person']), trim($p['contact_email']));
            flash('success', 'Partner company account created. Temporary password: ' . $password);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('index.php?r=admin_partners');
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
