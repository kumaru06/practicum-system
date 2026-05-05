<?php

namespace App\Http\Controllers;

use App\Services\PracticumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if ($user = $this->user()) {
            return redirect()->route(app(PracticumService::class)->routeForRole($user['role'] ?? null));
        }

        return view('auth.login', $this->portalViewData());
    }

    public function showAdminLogin(): View|RedirectResponse
    {
        return $this->showLoginFor('admin');
    }

    public function showCoordinatorLogin(): View|RedirectResponse
    {
        return $this->showLoginFor('coordinator');
    }

    public function showStudentLogin(): View|RedirectResponse
    {
        return $this->showLoginFor('student');
    }

    public function showPartnerLogin(): View|RedirectResponse
    {
        return $this->showLoginFor('partner');
    }

    public function login(Request $request, PracticumService $practicum): RedirectResponse
    {
        return back()->with('error', 'Please choose the correct login portal for your account.')->withInput($request->only('email'));
    }

    public function adminLogin(Request $request, PracticumService $practicum): RedirectResponse
    {
        return $this->loginFor($request, $practicum, 'admin');
    }

    public function coordinatorLogin(Request $request, PracticumService $practicum): RedirectResponse
    {
        return $this->loginFor($request, $practicum, 'coordinator');
    }

    public function studentLogin(Request $request, PracticumService $practicum): RedirectResponse
    {
        return $this->loginFor($request, $practicum, 'student');
    }

    public function partnerLogin(Request $request, PracticumService $practicum): RedirectResponse
    {
        return $this->loginFor($request, $practicum, 'partner');
    }

    private function showLoginFor(string $role): View|RedirectResponse
    {
        if ($user = $this->user()) {
            $practicum = app(PracticumService::class);
            if (($user['role'] ?? null) !== $role) {
                return redirect()->route($practicum->routeForRole($user['role'] ?? null))->with('error', 'You are already signed in to a different portal. Please log out before switching portals.');
            }

            return redirect()->route($practicum->routeForRole($user['role'] ?? null));
        }

        return view('auth.login', $this->portalViewData($role));
    }

    private function loginFor(Request $request, PracticumService $practicum, string $expectedRole): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = $practicum->userFindByEmail((string)$request->input('email'));

        if ($user && (int)$user['is_active'] === 1 && password_verify((string)$request->input('password'), $user['password_hash'])) {
            if (($user['role'] ?? '') !== $expectedRole) {
                $portal = $this->portalViewData((string)$user['role']);
                return back()->with('error', 'Invalid login portal for this account. Please use the ' . $portal['portalLabel'] . '.')->withInput($request->only('email'));
            }

            $request->session()->regenerate();
            session(['user' => ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role'], 'password_changed' => (int)($user['password_changed'] ?? 1)]]);
            return redirect()->route($practicum->routeForRole($user['role']));
        }

        return back()->with('error', 'Invalid credentials or inactive account.')->withInput($request->only('email'));
    }

    private function portalViewData(?string $role = null): array
    {
        $all = [
            'student'     => ['label' => 'Student Login Portal',         'route' => 'student.login',     'post' => 'student.login.post'],
            'admin'       => ['label' => 'Admin Login Portal',            'route' => 'admin.login',       'post' => 'admin.login.post'],
            'coordinator' => ['label' => 'OJT Coordinator Login Portal',  'route' => 'coordinator.login', 'post' => 'coordinator.login.post'],
            'partner'     => ['label' => 'Partner Company Login Portal',  'route' => 'partner.login',     'post' => 'partner.login.post'],
        ];

        // Admin portal is accessible by direct URL only — hidden from the public selector & switcher
        $visible = array_filter($all, fn($k) => $k !== 'admin', ARRAY_FILTER_USE_KEY);

        return [
            'portalRole'      => $role,
            'portalLabel'     => $role && isset($all[$role]) ? $all[$role]['label'] : 'Choose Login Portal',
            'loginPostRoute'  => $role && isset($all[$role]) ? $all[$role]['post'] : 'login.post',
            'portals'         => $visible,   // used for grid & tabs (no admin)
        ];
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
