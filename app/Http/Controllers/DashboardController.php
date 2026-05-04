<?php

namespace App\Http\Controllers;

use App\Services\PracticumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, PracticumService $practicum): RedirectResponse
    {
        $user = $this->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $freshUser = $practicum->userFind((int)$user['id']);
        session(['user.password_changed' => (int)($freshUser['password_changed'] ?? 1)]);
        $user = session('user');

        if ((int)($user['password_changed'] ?? 1) === 0) {
            return redirect()->route('student.password.edit');
        }

        if (($user['role'] ?? '') === 'student') {
            $student = $practicum->studentFindByUser((int)$user['id']);
            if ($student && (int)($student['profile_completed'] ?? 0) === 0) {
                return redirect()->route('student.profile');
            }
        }

        return redirect()->route($practicum->routeForRole($user['role'] ?? null));
    }

    public function redirectOldRoute(Request $request, PracticumService $practicum): RedirectResponse
    {
        $map = [
            'admin' => 'admin.dashboard',
            'admin_users' => 'admin.users',
            'admin_coordinators' => 'admin.coordinators',
            'admin_partners' => 'admin.partners',
            'admin_programs' => 'admin.programs',
            'admin_email_logs' => 'admin.email_logs',
            'admin_evaluations' => 'admin.evaluations',
            'coordinator' => 'coordinator.dashboard',
            'coordinator_manage' => 'coordinator.manage',
            'coordinator_students' => 'coordinator.students',
            'coordinator_evaluations' => 'coordinator.evaluations',
            'student' => 'student.dashboard',
            'student_profile' => 'student.profile',
            'partner' => 'partner.dashboard',
        ];

        $target = $map[(string)$request->query('r', '')] ?? null;
        if (!$target) {
            return $this->index($request, $practicum);
        }

        $params = [];
        if ($target === 'partner.dashboard' && $request->query('enrollment')) {
            $params['enrollment'] = $request->query('enrollment');
        }

        return redirect()->route($target, $params);
    }
}
