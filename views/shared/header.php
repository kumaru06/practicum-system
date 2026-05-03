<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'AMA Practicum System') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-page role-<?= e($user['role'] ?? 'guest') ?>">
<div class="app-shell">
    <aside class="sidebar">
        <button class="sidebar-toggle" type="button" aria-label="Collapse sidebar"><svg viewBox="0 0 24 24"><path d="M15.4 7.4 14 6l-6 6 6 6 1.4-1.4L10.8 12l4.6-4.6ZM20 4h-2v16h2V4Z"/></svg></button>
        <div class="brand">
            <span class="brand-mark"><svg viewBox="0 0 24 24"><path d="M12 2 3 7v10l9 5 9-5V7l-9-5Zm0 3.2 5.8 3.2-5.8 3.2-5.8-3.2L12 5.2Zm-6 5.9 4.5 2.5v4.9L6 16v-4.9Zm12 0V16l-4.5 2.5v-4.9L18 11.1Z"/></svg></span>
            <div><strong>AMA Computer College</strong><small>OJT Management</small></div>
        </div>
        <nav class="nav">
            <?php $role = $user['role'] ?? ''; $homeRoute = $role ?: 'admin'; $currentRoute = $_GET['r'] ?? $homeRoute; ?>
            <a class="nav-link <?= in_array($currentRoute, ['admin', 'coordinator', 'student', 'partner'], true) ? 'active' : '' ?>" href="index.php?r=<?= e($homeRoute) ?>"><svg viewBox="0 0 24 24"><path d="M4 13h7V4H4v9Zm0 7h7v-5H4v5Zm9 0h7v-9h-7v9Zm0-16v5h7V4h-7Z"/></svg><span>Dashboard</span></a>
            <?php if ($role === 'admin'):
                $userRoutes = ['admin_users', 'admin_coordinators', 'admin_partners'];
                $userGroupOpen = in_array($currentRoute, $userRoutes, true);
            ?><div class="nav-group <?= $userGroupOpen ? 'open' : '' ?>">
                <button class="nav-group-toggle" type="button">
                    <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3Zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3Zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5Z"/></svg>
                    <span>Manage Users</span>
                    <svg class="chevron" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>
                </button>
                <div class="nav-group-items">
                    <a class="nav-link nav-sub <?= $currentRoute === 'admin_users' ? 'active' : '' ?>" href="index.php?r=admin_users"><svg viewBox="0 0 24 24"><path d="M12 3 2 8l10 5 8-4v6h2V8L12 3Zm-6 9v4c2 3 10 3 12 0v-4l-6 3-6-3Z"/></svg><span>Students</span></a>
                    <a class="nav-link nav-sub <?= $currentRoute === 'admin_coordinators' ? 'active' : '' ?>" href="index.php?r=admin_coordinators"><svg viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-8 8c.8-4 3.8-6 8-6s7.2 2 8 6H4Z"/></svg><span>Coordinators</span></a>
                    <a class="nav-link nav-sub <?= $currentRoute === 'admin_partners' ? 'active' : '' ?>" href="index.php?r=admin_partners"><svg viewBox="0 0 24 24"><path d="M3 21V7l6-4 6 4v14H3Zm14 0V9h4v12h-4Z"/></svg><span>Partner Companies</span></a>
                </div>
            </div><?php endif; ?>
            <?php if ($role === 'admin'): ?><a class="nav-link <?= $currentRoute === 'admin_email_logs' ? 'active' : '' ?>" href="index.php?r=admin_email_logs"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2Zm0 4-8 5-8-5V6l8 5 8-5v2Z"/></svg><span>Email Logs</span></a><a class="nav-link <?= $currentRoute === 'admin_evaluations' ? 'active' : '' ?>" href="index.php?r=admin_evaluations"><svg viewBox="0 0 24 24"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17Z"/></svg><span>Evaluations</span></a><a class="nav-link <?= $currentRoute === 'admin_programs' ? 'active' : '' ?>" href="index.php?r=admin_programs"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4V5Zm2 2v10h12V7H6Zm2 2h8v2H8V9Zm0 4h6v2H8v-2Z"/></svg><span>Programs / Courses</span></a><?php endif; ?>
            <?php if ($role === 'coordinator'): ?><a class="nav-link <?= $currentRoute === 'coordinator_manage' ? 'active' : '' ?>" href="index.php?r=coordinator_manage"><svg viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-8 8c.8-4 3.8-6 8-6s7.2 2 8 6H4Z"/></svg><span>Coordinator</span></a><a class="nav-link <?= $currentRoute === 'coordinator_students' ? 'active' : '' ?>" href="index.php?r=coordinator_students"><svg viewBox="0 0 24 24"><path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/></svg><span>My Students</span></a><a class="nav-link <?= $currentRoute === 'coordinator_evaluations' ? 'active' : '' ?>" href="index.php?r=coordinator_evaluations"><svg viewBox="0 0 24 24"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17Z"/></svg><span>Evaluations</span></a><?php endif; ?>
            <?php if ($role === 'student'): ?><a class="nav-link <?= $currentRoute === 'student' ? 'active' : '' ?>" href="index.php?r=student"><svg viewBox="0 0 24 24"><path d="M12 3 2 8l10 5 8-4v6h2V8L12 3Zm-6 9v4c2 3 10 3 12 0v-4l-6 3-6-3Z"/></svg><span>Student Portal</span></a><?php endif; ?>
            <?php if ($role === 'partner'): ?><a class="nav-link <?= $currentRoute === 'partner' ? 'active' : '' ?>" href="index.php?r=partner"><svg viewBox="0 0 24 24"><path d="M3 21V7l6-4 6 4v14h-4v-5H7v5H3Zm14 0V9h4v12h-4ZM7 9h4v2H7V9Zm0 4h4v2H7v-2Z"/></svg><span>Company Portal</span></a><?php endif; ?>
        </nav>
        <div class="sidebar-user">
            <div class="sidebar-user-info">
                <span class="user-avatar"><?= e(strtoupper(substr($user['name'] ?? 'A', 0, 1))) ?></span>
                <div>
                    <strong><?= e($user['name'] ?? '') ?></strong>
                    <small><?= e(ucwords(str_replace('_', ' ', $user['role'] ?? ''))) ?></small>
                </div>
            </div>
            <a class="nav-link sidebar-logout" href="logout.php">
                <svg viewBox="0 0 24 24"><path d="M16 13v-2H7V8l-5 4 5 4v-3h9Zm1-9H9a2 2 0 0 0-2 2v3h2V6h8v12H9v-3H7v3a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Z"/></svg>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    <main class="main">
        <header class="topbar">
            <div class="topbar-copy"><h1><?= e($title ?? 'Dashboard') ?></h1><span><?= e(ucwords(str_replace('_', ' ', $user['role'] ?? ''))) ?></span></div>
            <div class="top-actions"><div class="user-chip"><span class="user-avatar"><?= e(strtoupper(substr($user['name'] ?? 'A', 0, 1))) ?></span><div><strong><?= e($user['name'] ?? '') ?></strong><small><?= e($user['email'] ?? '') ?></small></div></div></div>
        </header>
        <section class="content">
            <div class="toast-stack" aria-live="polite">
                <?php if ($m = flash('success')): ?><div class="toast success"><?= e($m) ?></div><?php endif; ?>
                <?php if ($m = flash('error')): ?><div class="toast danger"><?= e($m) ?></div><?php endif; ?>
            </div>
