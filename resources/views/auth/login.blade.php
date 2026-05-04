<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - AMA Practicum System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="login-page">
    <div class="login-split">
        <!-- Left: Image panel -->
        <div class="login-image-panel">
            <img src="{{ asset('assets/image/main/image.png') }}" alt="Practicum" class="login-hero-img">
            <div class="login-image-overlay">
                <div class="login-image-text">
                    <h1>AMA Practicum<br>Management System</h1>
                    <p>Track your OJT journey — from deployment to completion.</p>
                </div>
            </div>
        </div>
        <!-- Right: Form panel -->
        <div class="login-form-panel">
            <div class="login-card">
                <div class="brand login-brand">
                    <img src="{{ asset('assets/image/main/logo/amalogo.png') }}" alt="AMA Logo" class="login-logo">
                    <div><strong>Computer College</strong></div>
                </div>
                <?php if ($m = session('error')): ?><div class="alert danger"><?= e($m) ?></div><?php endif; ?>
                <form method="post" class="form js-validate">
                    @csrf
                    <label>Email<input required type="email" name="email" autocomplete="username"></label>
                    <label>Password<input required type="password" name="password" autocomplete="current-password"></label>
                    <button class="btn btn-primary" type="submit"><span class="btn-text">Sign in</span><span class="spinner"></span></button>
                </form>
            </div>
        </div>
    </div>
<script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
