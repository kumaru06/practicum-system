<?php
require_once __DIR__ . '/init.php';
require_login();

$route = $_GET['r'] ?? current_user()['role'];
$method = $_SERVER['REQUEST_METHOD'];

$freshUser = (new User(db()))->find((int)current_user()['id']);
$_SESSION['user']['password_changed'] = (int)($freshUser['password_changed'] ?? 1);

if ((int)current_user()['password_changed'] === 0) {
    if ($method === 'POST' && ($_POST['action'] ?? '') === 'student_change_password') {
        (new StudentController())->changePassword();
    }
    (new StudentController())->changePasswordForm();
    exit;
}

if (current_user()['role'] === 'student') {
    $studentRecord = (new Student(db()))->findByUser((int)current_user()['id']);
    if ($studentRecord && (int)($studentRecord['profile_completed'] ?? 0) === 0) {
        if ($method === 'POST' && ($_POST['action'] ?? '') === 'student_save_profile') {
            (new StudentController())->saveProfile();
        }
        (new StudentController())->profileForm();
        exit;
    }
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    match ($action) {
        'admin_create_coordinator' => (new AdminController())->createCoordinator(),
        'admin_create_company' => (new AdminController())->createCompany(),
        'admin_save_program' => (new AdminController())->saveProgram(),
        'admin_delete_program' => (new AdminController())->deleteProgram(),
        'admin_toggle_user' => (new AdminController())->toggleUser(),
        'coordinator_create_student' => (new CoordinatorController())->createStudent(),
        'coordinator_enroll_student' => (new CoordinatorController())->enrollStudent(),
        'coordinator_reset_password' => (new CoordinatorController())->resetStudentPassword(),
        'student_change_password' => (new StudentController())->changePassword(),
        'student_save_profile' => (new StudentController())->saveProfile(),
        'student_upload_requirement' => (new StudentController())->uploadRequirement(),
        'student_submit_requirements' => (new StudentController())->submitRequirements(),
        'coordinator_forward_deployment' => (new CoordinatorController())->forwardDeployment(),
        'partner_accept_deployment' => (new PartnerController())->acceptDeployment(),
        'partner_schedule_orientation' => (new PartnerController())->scheduleOrientation(),
        'partner_complete_orientation' => (new PartnerController())->completeOrientation(),
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
    'admin_programs' => (new AdminController())->managePrograms(),
    'admin_email_logs' => (new AdminController())->emailLogs(),
    'admin_evaluations' => (new AdminController())->evaluations(),
    'coordinator' => (new CoordinatorController())->dashboard(),
    'coordinator_manage' => (new CoordinatorController())->manage(),
    'coordinator_students' => (new CoordinatorController())->myStudents(),
    'coordinator_evaluations' => (new CoordinatorController())->evaluations(),
    'student' => (new StudentController())->dashboard(),
    'student_profile' => (new StudentController())->profileForm(),
    'partner' => (new PartnerController())->dashboard(),
    default => redirect('index.php?r=' . current_user()['role']),
};
