<?php
class AuthController extends BaseController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = (new User($this->db))->findByEmail($email);
            if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'password_changed' => (int)($user['password_changed'] ?? 1),
                ];
                redirect('index.php');
            }
            flash('error', 'Invalid credentials or inactive account.');
        }
        require __DIR__ . '/../views/shared/login.php';
    }
}
