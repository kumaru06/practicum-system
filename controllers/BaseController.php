<?php
abstract class BaseController
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $user = current_user();
        require __DIR__ . '/../views/shared/header.php';
        require __DIR__ . '/../views/' . $view . '.php';
        require __DIR__ . '/../views/shared/footer.php';
    }

    protected function post(): array
    {
        verify_csrf();
        return $_POST;
    }
}
