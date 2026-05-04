<?php
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $route): never
{
    header('Location: ' . $route);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            exit('Invalid CSRF token. Please go back and try again.');
        }
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        redirect('auth.php');
    }
}

function require_role(string|array $roles): void
{
    require_login();
    $roles = (array)$roles;
    if (!in_array(current_user()['role'], $roles, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function flash(?string $key = null, ?string $message = null): ?string
{
    if ($key && $message) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    if ($key && isset($_SESSION['flash'][$key])) {
        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $value;
    }
    return null;
}

function random_password(int $length = 12): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@$%';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $password;
}

function upload_cor(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('COR upload is required.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('COR file must not exceed 5MB.');
    }

    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('COR must be a PDF, JPG, or PNG file.');
    }

    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $targetDir = __DIR__ . '/uploads/cor';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $target = $targetDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Unable to save uploaded COR.');
    }
    return 'uploads/cor/' . $name;
}

function upload_document(array $file, string $folder = 'documents', bool $required = true): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            throw new RuntimeException('Document upload is required.');
        }
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Unable to read uploaded file.');
    }
    if ($file['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('Uploaded file must not exceed 8MB.');
    }

    $allowed = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Upload must be a PDF, JPG, or PNG file.');
    }

    $safeFolder = preg_replace('/[^a-z0-9_\/-]/i', '', $folder) ?: 'documents';
    $targetDir = __DIR__ . '/uploads/' . $safeFolder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $name)) {
        throw new RuntimeException('Unable to save uploaded file.');
    }
    return 'uploads/' . $safeFolder . '/' . $name;
}

function projected_ojt_end_date(string $startDate, int $requiredHours, int $hoursPerDay = 8): string
{
    $daysNeeded = max(1, (int)ceil($requiredHours / max(1, $hoursPerDay)));
    $date = new DateTimeImmutable($startDate);
    $workedDays = 0;
    while ($workedDays < $daysNeeded) {
        $weekday = (int)$date->format('N');
        if ($weekday <= 5) {
            $workedDays++;
        }
        if ($workedDays < $daysNeeded) {
            $date = $date->modify('+1 day');
        }
    }
    return $date->format('Y-m-d');
}
