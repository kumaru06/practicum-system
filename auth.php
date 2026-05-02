<?php
require_once __DIR__ . '/init.php';
if (current_user()) {
    redirect('index.php');
}
(new AuthController())->login();
