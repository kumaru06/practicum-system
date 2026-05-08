<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($portalLabel ?? 'Login') }} — AMA Practicum System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        /* ── Right-panel override: elevated card ── */
        .login-form-panel {
            background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 32px 40px;
        }

        .lc {
            width: min(420px, 100%);
            background: #fff;
            border-radius: 24px;
            padding: 40px 40px 36px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.05), 0 20px 50px -10px rgba(0,0,0,.12);
            border: 1px solid rgba(0,0,0,.05);
        }

        /* ── Logo / brand area ── */
        .lc-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            margin-bottom: 28px;
        }
        .lc-logo {
            width: 90px;
            height: auto;
        }
        .lc-school {
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #8B1A1A;
        }

        /* ── Portal badge ── */
        .portal-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #fff7f7;
            border: 1px solid #fecaca;
            color: #8B1A1A;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 999px;
            margin-bottom: 12px;
        }
        .portal-badge span.dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #8B1A1A;
            flex-shrink: 0;
        }

        /* ── Heading / subtitle ── */
        .lc-heading {
            font-size: 1.45rem;
            font-weight: 800;
            color: #8B1A1A;
            margin: 0 0 6px;
            letter-spacing: -.025em;
            line-height: 1.2;
        }
        .lc-sub {
            font-size: .83rem;
            color: #8B1A1A;
            margin: 0 0 24px;
            line-height: 1.55;
        }

        /* ── Alert ── */
        .lc-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: .83rem;
            font-weight: 600;
            color: #991b1b;
            line-height: 1.5;
        }
        .lc-alert svg { flex-shrink: 0; margin-top: 1px; }

        /* ── Field group – floating label ── */
        .lc-field {
            position: relative;
            margin-bottom: 20px;
        }
        .lc-label {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: .93rem;
            font-weight: 500;
            color: #9ca3af;
            pointer-events: none;
            transition: top .18s ease, font-size .18s ease, color .18s ease, font-weight .18s ease;
            background: transparent;
            padding: 0 3px;
            line-height: 1;
        }
        /* Float up when focused or filled */
        .lc-input:focus ~ .lc-label,
        .lc-input:not(:placeholder-shown) ~ .lc-label {
            top: 0;
            font-size: .72rem;
            font-weight: 700;
            color: #8B1A1A;
            background: #fff;
            letter-spacing: .03em;
            text-transform: uppercase;
        }
        .lc-input {
            width: 100%;
            padding: 18px 14px 8px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-family: inherit;
            font-size: .93rem;
            color: #111827;
            background: #f9fafb;
            transition: border-color .15s, box-shadow .15s, background .15s;
            box-sizing: border-box;
            outline: none;
        }
        .lc-input:focus {
            border-color: #8B1A1A;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(139,26,26,.1);
        }
        .lc-input::placeholder { color: transparent; }

        /* ── Sign in button ── */
        .lc-btn {
            width: 100%;
            margin-top: 8px;
            padding: 13px;
            background: #8B1A1A;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: inherit;
            font-size: .95rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background .18s, transform .15s, box-shadow .18s;
            box-shadow: 0 4px 14px rgba(139,26,26,.28);
            letter-spacing: .01em;
        }
        .lc-btn:hover { background: #7a1616; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(139,26,26,.35); }
        .lc-btn:active { transform: translateY(0); }
        .lc-btn .spinner { display: none; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
        .lc-btn.loading .btn-text { opacity: 0; }
        .lc-btn.loading .spinner { display: block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Portal switcher (tabs) ── */
        .lc-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 22px 0 16px;
            color: #9ca3af;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .lc-divider::before, .lc-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .lc-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .lc-tab {
            flex: 1 1 calc(50% - 4px);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 10px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            background: #f9fafb;
            color: #6b7280;
            text-decoration: none;
            font-size: .75rem;
            font-weight: 700;
            transition: all .18s;
            min-width: 0;
            text-align: center;
        }
        .lc-tab:hover { border-color: #8B1A1A; color: #8B1A1A; background: #fff7f7; }
        .lc-tab.active { border-color: #8B1A1A; background: #8B1A1A; color: #fff; }
        .lc-tab svg { flex-shrink: 0; }

        /* ── Portal selection grid (no-portal state) ── */
        .portal-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 4px;
        }
        .portal-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            color: #111827;
            text-decoration: none;
            font-weight: 700;
            font-size: .9rem;
            background: #f9fafb;
            transition: all .18s;
        }
        .portal-link:hover { border-color: #8B1A1A; background: #fff7f7; color: #8B1A1A; transform: translateX(3px); }
        .portal-link-icon {
            width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
            background: #fff7f7;
            border-radius: 10px;
            border: 1.5px solid #fecaca;
            flex-shrink: 0;
            color: #8B1A1A;
            transition: border-color .18s, background .18s;
        }
        .portal-link:hover .portal-link-icon { border-color: #8B1A1A; background: #fee2e2; }
        .portal-link-text { display: flex; flex-direction: column; gap: 2px; }
        .portal-link-title { font-size: .9rem; font-weight: 800; color: #8B1A1A; }
        .portal-link-desc { font-size: .72rem; font-weight: 500; color: #9ca3af; }
        .portal-link:hover .portal-link-desc { color: #8B1A1A; }
        .portal-link-arrow { margin-left: auto; color: #d1d5db; transition: color .18s, transform .18s; }
        .portal-link:hover .portal-link-arrow { color: #8B1A1A; transform: translateX(2px); }

        .login-image-text {
            max-width: 520px;
        }

        .login-image-panel {
            isolation: isolate;
        }

        @media (max-width: 1024px) {
            .login-form-panel {
                padding: 28px;
            }

            .lc {
                padding: 32px 28px 28px;
            }
        }

        @media (max-width: 768px) {
            .login-page {
                min-height: 100dvh;
                background:
                    linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            }

            .login-split {
                display: block;
                min-height: 100dvh;
                height: auto;
                overflow: visible;
            }

            .login-image-panel {
                display: none;
            }

            .login-form-panel {
                display: grid;
                place-items: center;
                min-height: 100dvh;
                margin-top: 0;
                padding: 24px 16px;
                background: transparent;
                position: relative;
                z-index: 2;
            }

            .lc {
                width: 100%;
                max-width: 430px;
                padding: 26px 20px 22px;
                border-radius: 28px;
                box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
            }

            .lc-brand {
                margin-bottom: 20px;
            }

            .lc-logo {
                width: 78px;
            }

            .lc-school {
                font-size: .74rem;
            }

            .lc-heading {
                font-size: 1.7rem;
                line-height: 1.15;
            }

            .lc-sub {
                font-size: .82rem;
                margin-bottom: 18px;
            }

            .portal-grid {
                gap: 12px;
            }

            .portal-link {
                padding: 14px;
                gap: 12px;
                align-items: center;
                border-radius: 16px;
            }

            .portal-link-title {
                font-size: .92rem;
                line-height: 1.35;
            }

            .portal-link-desc {
                font-size: .74rem;
                line-height: 1.35;
            }

            .portal-link-arrow {
                flex-shrink: 0;
            }

            .lc-tabs {
                display: grid;
                grid-template-columns: 1fr;
            }

            .lc-tab {
                flex: initial;
                padding: 11px 12px;
                font-size: .8rem;
            }
        }

        @media (max-width: 480px) {
            .login-form-panel {
                padding: 18px 12px;
            }

            .lc {
                padding: 24px 16px 18px;
                border-radius: 24px;
            }

            .lc-heading {
                font-size: 1.45rem;
            }

            .portal-link-icon {
                width: 36px;
                height: 36px;
            }
        }
    </style>
</head>
<body class="login-page">
    <div class="login-split">
        <!-- Left: Image panel — untouched -->
        <div class="login-image-panel">
            <img src="{{ asset('assets/image/main/image.png') }}" alt="Practicum" class="login-hero-img">
            <div class="login-image-overlay">
                <div class="login-image-text">
                    <h1>AMA Practicum<br>Management System</h1>
                    <p>Track your OJT journey — from deployment to completion.</p>
                </div>
            </div>
        </div>

        <!-- Right: Redesigned form panel -->
        <div class="login-form-panel">
            <div class="lc">

                {{-- Brand --}}
                <div class="lc-brand">
                    <img src="{{ asset('assets/image/main/logo/amalogo.png') }}" alt="AMA Logo" class="lc-logo">
                    <span class="lc-school">Computer College</span>
                </div>

                {{-- Portal badge --}}
                @if (!empty($portalRole))
                    <div class="portal-badge"><span class="dot"></span> {{ $portalLabel ?? 'Portal' }}</div>
                @endif

                {{-- Heading (only on portal-picker screen) --}}
                @if (empty($portalRole))
                <h1 class="lc-heading">Choose your login portal</h1>
                <p class="lc-sub">Select the portal that matches your account role. Other account types will be blocked.</p>
                @endif

                {{-- Error alert --}}
                @if ($m = session('error'))
                    <div class="lc-alert">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $m }}
                    </div>
                @endif

                @if (empty($portalRole))
                    {{-- Portal selector --}}
                    @php
                        $portalMeta = [
                            'student'     => ['desc' => 'OJT student account',       'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>'],
                            'admin'       => ['desc' => 'System administrator',       'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'],
                            'coordinator' => ['desc' => 'OJT coordinator account',    'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'],
                            'partner'     => ['desc' => 'Partner company account',    'icon' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>'],
                        ];
                    @endphp
                    <div class="portal-grid">
                        @foreach (($portals ?? []) as $role => $portal)
                            <a class="portal-link" href="{{ route($portal['route']) }}">
                                <span class="portal-link-icon">{!! $portalMeta[$role]['icon'] ?? '' !!}</span>
                                <span class="portal-link-text">
                                    <span class="portal-link-title">{{ $portal['label'] }}</span>
                                    <span class="portal-link-desc">{{ $portalMeta[$role]['desc'] ?? '' }}</span>
                                </span>
                                <svg class="portal-link-arrow" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                            </a>
                        @endforeach
                    </div>

                @else
                    {{-- Login form --}}
                    <form method="post" action="{{ route($loginPostRoute ?? 'login.post') }}" id="loginForm" novalidate>
                        @csrf
                        <div class="lc-field">
                            <input class="lc-input" id="login-email" required type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder=" ">
                            <label class="lc-label" for="login-email">Email address</label>
                        </div>
                        <div class="lc-field">
                            <input class="lc-input" id="login-password" required type="password" name="password" autocomplete="current-password" placeholder=" ">
                            <label class="lc-label" for="login-password">Password</label>
                        </div>
                        <button type="submit" class="lc-btn" id="loginBtn">
                            <span class="btn-text">Sign in</span>
                            <span class="spinner"></span>
                        </button>
                    </form>

                    {{-- Portal switcher (hidden on admin portal) --}}
                    @if ($portalRole !== 'admin')
                    <div class="lc-divider">Switch portal</div>
                    <div class="lc-tabs">
                        @php
                            $tabIcons = [
                                'student'     => '<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
                                'admin'       => '<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
                                'coordinator' => '<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                                'partner'     => '<svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>',
                            ];
                            $tabShort = ['student' => 'Student', 'admin' => 'Admin', 'coordinator' => 'Coordinator', 'partner' => 'Partner'];
                        @endphp
                        @foreach (($portals ?? []) as $role => $portal)
                            <a class="lc-tab {{ $portalRole === $role ? 'active' : '' }}" href="{{ route($portal['route']) }}">
                                {!! $tabIcons[$role] ?? '' !!}
                                {{ $tabShort[$role] ?? $role }}
                            </a>
                        @endforeach
                    </div>
                    @endif
                @endif

            </div>
        </div>
    </div>

<script src="{{ asset('assets/js/main.js') }}"></script>
<script>
    // Spinner on form submit
    const form = document.getElementById('loginForm');
    const btn  = document.getElementById('loginBtn');
    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.classList.add('loading');
            btn.disabled = true;
        });
    }
</script>
</body>
</html>
