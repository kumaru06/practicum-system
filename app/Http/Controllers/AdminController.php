<?php

namespace App\Http\Controllers;

use App\Services\PracticumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AdminController extends Controller
{
    public function dashboard(PracticumService $p): View
    {
        $this->requireRole($p, 'admin');
        return $this->renderNative($p, 'admin.dashboard', ['title' => 'Admin Dashboard', 'stats' => ['coordinators' => $p->userCountRole('coordinator'), 'companies' => $p->companyCount(), 'students' => $p->userCountRole('student'), 'active' => $p->enrollmentActiveCount()], 'companies' => $p->companiesAll(), 'charts' => ['statusDistribution' => $p->enrollmentStatusDistribution(), 'completionRates' => $p->enrollmentCompletionRatesByCourse(), 'monthlyTrends' => $p->enrollmentMonthlyTrends(), 'courseStudents' => $p->enrollmentStudentProgressByCourse()]]);
    }

    public function users(PracticumService $p): View { $this->requireRole($p, 'admin'); return $this->renderNative($p, 'admin.users', ['title' => 'Manage Student', 'allUsers' => $p->usersAllStudents()]); }
    public function coordinators(PracticumService $p): View { $this->requireRole($p, 'admin'); return $this->renderNative($p, 'admin.coordinators', ['title' => 'Manage Coordinators', 'coordinators' => $p->usersByRole('coordinator')]); }
    public function partners(PracticumService $p): View { $this->requireRole($p, 'admin'); return $this->renderNative($p, 'admin.partners', ['title' => 'Manage Companies', 'partners' => $p->companiesAll(), 'programs' => $p->programsAll(true)]); }
    public function programs(PracticumService $p): View { $this->requireRole($p, 'admin'); return $this->renderNative($p, 'admin.programs', ['title' => 'Programs / Courses', 'programs' => $p->programsAll()]); }
    public function evaluations(PracticumService $p): View { $this->requireRole($p, 'admin'); return $this->renderNative($p, 'admin.evaluations', ['title' => 'Evaluations', 'evaluations' => $p->evaluationsAllWithDetails()]); }

    public function emailLogs(Request $request, PracticumService $p): View
    {
        $this->requireRole($p, 'admin');
        $filters = ['type' => trim((string)$request->query('type', '')), 'status' => trim((string)$request->query('status', '')), 'date_from' => trim((string)$request->query('date_from', '')), 'date_to' => trim((string)$request->query('date_to', ''))];
        return $this->renderNative($p, 'admin.email_logs', ['title' => 'Email Logs', 'logs' => $p->emailLogsFiltered($filters), 'filters' => $filters, 'types' => $p->emailLogTypes()]);
    }

    public function createCoordinator(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'admin');
        try {
            $password = $p->randomPassword();
            $name = trim((string)$request->input('name'));
            $email = trim((string)$request->input('email'));
            $userId = $p->userCreate($name, $email, $password, 'coordinator', (int)$user['id'], 0);
            \Illuminate\Support\Facades\DB::table('coordinators')->insert(['user_id' => $userId, 'department' => trim((string)$request->input('department', 'OJT Department')) ?: 'OJT Department']);
            $p->emailSend($email, 'Your AMA Practicum Coordinator Account', 'account_credentials', 'account_credentials', ['name' => $name, 'email' => $email, 'password' => $password, 'roleLabel' => 'OJT Coordinator']);
            return redirect()->route('admin.coordinators')->with('success', 'Coordinator account created and credentials email was processed.');
        } catch (Throwable $e) {
            return redirect()->route('admin.coordinators')->with('error', $e->getMessage());
        }
    }

    public function createCompany(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'admin');
        try {
            $programIds = (array)$request->input('program_ids', []);
            if (!$programIds) throw new \RuntimeException('Select at least one accepted program/course.');
            $contactPerson = trim((string)$request->input('contact_person', $request->input('name', '')));
            $contactNumber = $p->formatPhilippineMobile((string)$request->input('contact_number', ''));
            $password = $p->randomPassword();
            $companyName = trim((string)$request->input('company_name'));
            $contactEmail = trim((string)$request->input('contact_email'));
            $userId = $p->userCreate($companyName, $contactEmail, $password, 'partner', (int)$user['id'], 0);
            $p->companyCreate($userId, $companyName, trim((string)$request->input('address', '')), $contactPerson, $contactEmail, $contactNumber, $programIds);
            $sent = $p->emailSend($contactEmail, 'Your AMA Practicum Partner Account', 'account_credentials', 'account_credentials', ['name' => $contactPerson, 'email' => $contactEmail, 'password' => $password, 'roleLabel' => 'Industry Partner']);
            return redirect()->route('admin.partners')->with($sent ? 'success' : 'error', $sent ? 'Partner company account created and credentials email was sent to ' . $contactEmail . '.' : 'Partner company account created, but the credentials email failed. Check Email Logs.');
        } catch (Throwable $e) {
            return redirect()->route('admin.partners')->with('error', $e->getMessage());
        }
    }

    public function resendCompanyCredentials(Request $request, PracticumService $p): RedirectResponse
    {
        $this->requireRole($p, 'admin');
        try {
            $company = $p->companyFind((int)$request->input('company_id'));
            if (!$company) throw new \RuntimeException('Partner company not found.');
            $password = $p->randomPassword();
            $p->userUpdatePassword((int)$company['user_id'], $password, 0);
            $sent = $p->emailSend($company['contact_email'], 'Your AMA Practicum Partner Account', 'account_credentials', 'account_credentials', ['name' => $company['contact_person'], 'email' => $company['contact_email'], 'password' => $password, 'roleLabel' => 'Industry Partner']);
            return redirect()->route('admin.partners')->with($sent ? 'success' : 'error', $sent ? 'Partner credentials were resent to ' . $company['contact_email'] . '.' : 'Credentials were reset, but the email failed. Check Email Logs.');
        } catch (Throwable $e) {
            return redirect()->route('admin.partners')->with('error', $e->getMessage());
        }
    }

    public function resetUserCredentials(Request $request, PracticumService $p): RedirectResponse
    {
        $this->requireRole($p, 'admin');
        $redirect = (string)$request->input('redirect', 'admin.users');

        try {
            $target = $p->userFind((int)$request->input('user_id'));
            if (!$target || ($target['role'] ?? '') === 'admin') throw new \RuntimeException('User account cannot be reset.');

            $password = $p->randomPassword();
            $p->userUpdatePassword((int)$target['id'], $password, 0);
            $roleLabel = match ($target['role']) {
                'coordinator' => 'OJT Coordinator',
                'student' => 'Student',
                'partner' => 'Industry Partner',
                default => ucwords(str_replace('_', ' ', (string)$target['role'])),
            };
            $sent = $p->emailSend($target['email'], 'Your AMA Practicum Account Credentials', 'account_credentials', 'account_credentials', ['name' => $target['name'], 'email' => $target['email'], 'password' => $password, 'roleLabel' => $roleLabel]);
            return redirect()->route(str_contains($redirect, '.') ? $redirect : 'admin.users')->with($sent ? 'success' : 'error', $sent ? 'Temporary credentials were sent to ' . $target['email'] . '.' : 'Password was reset, but the email failed. Check Email Logs.');
        } catch (Throwable $e) {
            return redirect()->route(str_contains($redirect, '.') ? $redirect : 'admin.users')->with('error', $e->getMessage());
        }
    }

    public function toggleUser(Request $request, PracticumService $p): RedirectResponse
    {
        $this->requireRole($p, 'admin');
        $p->userSetActive((int)$request->input('user_id'), (int)$request->input('active'));
        $redirect = (string)$request->input('redirect', 'admin.users');
        return redirect()->route(str_contains($redirect, '.') ? $redirect : 'admin.users')->with('success', 'User status updated.');
    }

    public function saveProgram(Request $request, PracticumService $p): RedirectResponse
    {
        $this->requireRole($p, 'admin');
        try {
            if ($request->filled('program_id')) {
                $p->programUpdate((int)$request->input('program_id'), (string)$request->input('code'), (string)$request->input('name'), (int)$request->input('required_hours'), (int)$request->input('is_active', 1));
                $message = 'Program updated.';
            } else {
                $p->programCreate((string)$request->input('code'), (string)$request->input('name'), (int)$request->input('required_hours'));
                $message = 'Program created.';
            }
            return redirect()->route('admin.programs')->with('success', $message);
        } catch (Throwable $e) {
            return redirect()->route('admin.programs')->with('error', $e->getMessage());
        }
    }

    public function deleteProgram(Request $request, PracticumService $p): RedirectResponse
    {
        $this->requireRole($p, 'admin');
        try {
            $p->programDelete((int)$request->input('program_id'));
            return redirect()->route('admin.programs')->with('success', 'Program deleted.');
        } catch (Throwable) {
            return redirect()->route('admin.programs')->with('error', 'Program is already in use. Deactivate it instead.');
        }
    }
}
