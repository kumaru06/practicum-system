<?php
require_once __DIR__ . '/init.php';
require_login();

$route = $_GET['r'] ?? current_user()['role'];
$method = $_SERVER['REQUEST_METHOD'];

if (current_user()['role'] === 'student') {
    $freshUser = (new User(db()))->find((int)current_user()['id']);
    $_SESSION['user']['password_changed'] = (int)($freshUser['password_changed'] ?? 1);
}

if (current_user()['role'] === 'student' && (int)current_user()['password_changed'] === 0) {
    if ($method === 'POST' && ($_POST['action'] ?? '') === 'student_change_password') {
        (new StudentController())->changePassword();
    }
    (new StudentController())->changePasswordForm();
    exit;
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    match ($action) {
        'admin_create_coordinator' => (new AdminController())->createCoordinator(),
        'admin_create_company' => (new AdminController())->createCompany(),
        'admin_toggle_user' => (new AdminController())->toggleUser(),
        'coordinator_create_student' => (new CoordinatorController())->createStudent(),
        'coordinator_enroll_student' => (new CoordinatorController())->enrollStudent(),
        'coordinator_reset_password' => (new CoordinatorController())->resetStudentPassword(),
        'student_change_password' => (new StudentController())->changePassword(),
        'student_add_dtr' => (new StudentController())->addDtr(),
        'student_add_weekly' => (new StudentController())->addWeekly(),
        'partner_submit_evaluation' => (new PartnerController())->submitEvaluation(),
        default => exit('Unknown action'),
    };
}

match ($route) {
    'admin' => (new AdminController())->dashboard(),
    'admin_users' => (new AdminController())->manageUsers(),
    'admin_coordinators' => (new AdminController())->manageCoordinators(),
    'admin_partners' => (new AdminController())->managePartners(),
    'admin_email_logs' => (new AdminController())->emailLogs(),
    'admin_evaluations' => (new AdminController())->evaluations(),
    'coordinator' => (new CoordinatorController())->dashboard(),
    'coordinator_students' => (new CoordinatorController())->myStudents(),
    'coordinator_evaluations' => (new CoordinatorController())->evaluations(),
    'student' => (new StudentController())->dashboard(),
    'partner' => (new PartnerController())->dashboard(),
    default => redirect('index.php?r=' . current_user()['role']),
};
