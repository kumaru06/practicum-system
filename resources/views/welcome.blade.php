<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AMA Practicum System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v=20260504-auditfix">
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            margin: 0;
            padding: 24px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(139, 26, 26, .18), transparent 34%),
                linear-gradient(135deg, #fff7f7, #f8fafc);
            color: #2b0f12;
        }

        .landing-card {
            width: min(940px, 100%);
            background: rgba(255, 255, 255, .94);
            border: 1px solid #f0dada;
            border-radius: 30px;
            padding: clamp(28px, 5vw, 52px);
            box-shadow: 0 24px 70px rgba(139, 26, 26, .16);
            display: grid;
            gap: 24px;
            overflow: hidden;
            position: relative;
        }

        .landing-card::after {
            content: '';
            position: absolute;
            right: -90px;
            bottom: -90px;
            width: 260px;
            height: 260px;
            border-radius: 999px;
            background: rgba(139, 26, 26, .08);
        }

        .landing-content {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 22px;
        }

        .landing-pill {
            display: inline-flex;
            width: fit-content;
            padding: 8px 14px;
            border-radius: 999px;
            background: #fee2e2;
            color: #8b1a1a;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(34px, 6vw, 62px);
            line-height: 1.02;
            max-width: 780px;
            letter-spacing: -.04em;
        }

        p {
            margin: 0;
            color: #6b7280;
            font-size: 17px;
            max-width: 720px;
            line-height: 1.65;
        }

        .landing-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 6px;
        }

        .landing-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 22px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 800;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .landing-btn:hover {
            transform: translateY(-1px);
        }

        .landing-btn.primary {
            background: #8b1a1a;
            color: #fff;
            box-shadow: 0 12px 26px rgba(139, 26, 26, .24);
        }

        .landing-btn.secondary {
            background: #fff;
            color: #8b1a1a;
            border: 1px solid #f0caca;
        }

        .landing-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            color: #8b1a1a;
            font-size: 13px;
            font-weight: 700;
        }

        .landing-meta span {
            border: 1px solid #f3d4d4;
            background: #fffafa;
            border-radius: 999px;
            padding: 8px 12px;
        }
    </style>
</head>
<body>
    <main class="landing-card" aria-label="AMA Practicum System landing page">
        <div class="landing-content">
            <span class="landing-pill">AMA OJT Portal</span>
            <h1>Practicum monitoring, deployment, and evaluation in one Laravel system.</h1>
            <p>
                Manage coordinators, students, company partners, requirements, weekly reports,
                evaluations, notifications, and email delivery from a modern Laravel-native portal.
            </p>
            <div class="landing-actions">
                <a class="landing-btn primary" href="{{ route('dashboard') }}">Open Dashboard</a>
                <a class="landing-btn secondary" href="{{ route('login') }}">Sign In</a>
            </div>
            <div class="landing-meta" aria-label="Available portals">
                <span>Admin</span>
                <span>OJT Coordinator</span>
                <span>Student</span>
                <span>Company Partner</span>
            </div>
        </div>
    </main>
</body>
</html>
