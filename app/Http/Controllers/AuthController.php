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

        return view('auth.login');
    }

    public function login(Request $request, PracticumService $practicum): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $user = $practicum->userFindByEmail((string)$request->input('email'));

        if ($user && (int)$user['is_active'] === 1 && password_verify((string)$request->input('password'), $user['password_hash'])) {
            $request->session()->regenerate();
            session(['user' => ['id' => (int)$user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role'], 'password_changed' => (int)($user['password_changed'] ?? 1)]]);
            return redirect()->route($practicum->routeForRole($user['role']));
        }

        return back()->with('error', 'Invalid credentials or inactive account.')->withInput($request->only('email'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
