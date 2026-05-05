<?php

namespace App\Http\Controllers;

use App\Services\PracticumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class StudentController extends Controller
{
    public function dashboard(PracticumService $p): View
    {
        return $this->renderNative($p, 'student.dashboard', $this->studentPageData($p, 'Student Dashboard'));
    }

    public function portal(PracticumService $p): RedirectResponse
    {
        $this->requireRole($p, 'student');
        return redirect()->route('student.documents');
    }

    public function records(PracticumService $p): View
    {
        return $this->renderNative($p, 'student.records', $this->studentPageData($p, 'Submit Record'));
    }

    public function timeline(PracticumService $p): View
    {
        return $this->renderNative($p, 'student.timeline', $this->studentPageData($p, 'Activity Timeline'));
    }

    public function documents(PracticumService $p): View
    {
        return $this->renderNative($p, 'student.documents', $this->studentPageData($p, 'Documents'));
    }

    public function settings(PracticumService $p): View
    {
        return $this->renderNative($p, 'student.settings', $this->studentPageData($p, 'Settings'));
    }

    public function profile(PracticumService $p): View
    {
        $user = $this->requireRole($p, 'student');
        $student = $p->studentFindByUser((int)$user['id']);
        return $this->renderNative($p, 'student.profile', [
            'title' => !empty($student['profile_completed']) ? 'Edit Student Profile' : 'Complete Student Profile',
            'student' => $student,
            'profileCompleted' => !empty($student['profile_completed']),
        ]);
    }

    public function password(PracticumService $p): View
    {
        $this->requireRole($p, ['student', 'coordinator', 'partner']);
        return $this->renderNative($p, 'student.change_password', ['title' => 'Change Temporary Password']);
    }

    public function changePassword(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, ['student', 'coordinator', 'partner']);
        $password = (string)$request->input('password');
        if (strlen($password) < 8 || $password !== (string)$request->input('confirm_password')) {
            return redirect()->route('student.password.edit')->with('error', strlen($password) < 8 ? 'Password must be at least 8 characters.' : 'Passwords do not match.');
        }
        $p->userUpdatePassword((int)$user['id'], $password, 1);
        session(['user.password_changed' => 1]);
        return redirect()->route($p->routeForRole($user['role']))->with('success', 'Password changed successfully. You can now access your dashboard.');
    }

    public function saveProfile(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'student');
        $student = $p->studentFindByUser((int)$user['id']);
        if (!$student) return redirect()->route('dashboard')->with('error', 'Student record not found.');
        $wasCompleted = !empty($student['profile_completed']);
        $request->validate([
            'photo_file' => [empty($student['photo_file']) ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:8192'],
            'address' => ['required', 'string', 'max:500'],
            'contact_number' => ['required', 'string', 'max:30'],
            'emergency_contact_name' => ['required', 'string', 'max:120'],
            'emergency_contact_number' => ['required', 'string', 'max:30'],
            'guardian_name' => ['required', 'string', 'max:120'],
            'guardian_contact' => ['required', 'string', 'max:30'],
            'year_level' => ['required', 'string', 'max:40'],
            'section' => ['required', 'string', 'max:40'],
        ]);
        try {
            $photo = $request->file('photo_file') ? $p->uploadDocument($request->file('photo_file'), 'profiles', false) : null;
            $p->studentUpdateProfile((int)$student['id'], $request->all(), $photo);
            if ($wasCompleted) {
                return redirect()->route('student.profile')->with('success', 'Profile updated successfully.');
            }
            return redirect()->route('student.dashboard')->with('success', 'Profile completed. Your dashboard is now unlocked.');
        } catch (Throwable $e) {
            return redirect()->route('student.profile')->with('error', $e->getMessage());
        }
    }

    public function uploadRequirement(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'student');
        try {
            $student = $p->studentFindByUser((int)$user['id']);
            if (!$student) throw new \RuntimeException('Student record not found.');
            if (!$p->enrollmentDetailsByStudent((int)$student['id'])) throw new \RuntimeException('You must be enrolled in OJT before uploading pre-deployment requirements.');
            $requirementKey = trim((string)$request->input('requirement_key'));
            if (!$p->studentCanUploadRequirement((int)$student['id'], $requirementKey)) throw new \RuntimeException($p->studentRequirementUploadMessage((int)$student['id'], $requirementKey) . '.');
            $path = $p->uploadDocument($request->file('requirement_file'), 'requirements/' . (int)$student['id']);
            $p->studentSaveRequirement((int)$student['id'], $requirementKey, (string)$path);
            return redirect()->route('student.portal')->with('success', 'Requirement uploaded.');
        } catch (Throwable $e) {
            return redirect()->route('student.portal')->with('error', $e->getMessage());
        }
    }

    public function submitRequirements(PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'student');
        $student = $p->studentFindByUser((int)$user['id']);
        if (!$student || !$p->studentHasCompleteRequirements((int)$student['id'])) return redirect()->route('student.portal')->with('error', 'Upload all five requirements before submitting for review.');
        $enrollment = $p->enrollmentDetailsByStudent((int)$student['id']);
        if (!$enrollment) return redirect()->route('student.portal')->with('error', 'You must be enrolled in OJT before submitting pre-deployment requirements.');
        $predeploymentStatus = $enrollment['predeployment_status'] ?? 'not_submitted';
        if ($p->studentHasApprovedRequirements((int)$student['id'])) return redirect()->route('student.portal')->with('success', 'All documents have already been approved. No need to submit again.');
        if ($predeploymentStatus === 'submitted') return redirect()->route('student.portal')->with('error', 'Your documents are already under coordinator review.');
        if ($predeploymentStatus === 'needs_revision') return redirect()->route('student.portal')->with('error', 'Replace the rejected document first. Only rejected documents are unlocked.');
        if (in_array($predeploymentStatus, ['approved', 'forwarded', 'accepted', 'orientation_scheduled', 'orientation_completed'], true)) return redirect()->route('student.portal')->with('success', 'Your documents are already approved or in deployment processing.');
        $p->enrollmentSetPredeploymentStatus((int)$student['id'], 'submitted');
        $p->notificationCreate((int)$student['coordinator_id'], 'Pre-deployment review requested', $student['name'] . ' submitted all pre-deployment requirements for review.', route('coordinator.students'));
        return redirect()->route('student.portal')->with('success', 'Pre-deployment requirements submitted for coordinator review.');
    }

    public function addDtr(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'student');
        $student = $p->studentFindByUser((int)$user['id']);
        if (!$student) return redirect()->route('student.records')->with('error', 'Student record not found.');
        $enrollment = $p->enrollmentDetailsByStudent((int)$student['id']);
        if (!$p->studentCanSubmitOjtReports((int)$student['id'])) return redirect()->route('student.records')->with('error', $p->enrollmentReportLockMessage($enrollment));
        $workDate = (string)$request->input('work_date');
        $officialStart = $enrollment['official_start_date'] ?? $enrollment['start_date'] ?? null;
        if ($officialStart && strtotime($workDate) < strtotime((string)$officialStart)) return redirect()->route('student.records')->with('error', 'DTR date cannot be earlier than your official OJT start date.');
        $p->reportAddDtr((int)$student['id'], (string)$request->input('work_date'), (string)$request->input('time_in'), (string)$request->input('time_out'), trim((string)$request->input('tasks_done')));
        $p->enrollmentSyncCompletion((int)$student['id']);
        return redirect()->route('student.records')->with('success', 'Daily time record submitted.');
    }

    public function addWeekly(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'student');
        $student = $p->studentFindByUser((int)$user['id']);
        if (!$student) return redirect()->route('student.records')->with('error', 'Student record not found.');
        if (!$p->studentCanSubmitOjtReports((int)$student['id'])) return redirect()->route('student.records')->with('error', $p->enrollmentReportLockMessage($p->enrollmentDetailsByStudent((int)$student['id'])));
        try {
            $path = $p->uploadWeeklyReport($request->file('report_file'));
            $p->reportAddWeekly((int)$student['id'], (int)$request->input('week_no'), trim((string)$request->input('report_text', '')), $path);
            return redirect()->route('student.records')->with('success', 'Weekly report submitted.');
        } catch (Throwable $e) {
            return redirect()->route('student.records')->with('error', $e->getMessage());
        }
    }

    public function uploadReport(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'student');
        $student = $p->studentFindByUser((int)$user['id']);
        if (!$student) return redirect()->route('student.records')->with('error', 'Student record not found.');
        if (!$p->studentCanSubmitOjtReports((int)$student['id'])) return redirect()->route('student.records')->with('error', $p->enrollmentReportLockMessage($p->enrollmentDetailsByStudent((int)$student['id'])));
        try {
            $path = $p->uploadWeeklyReport($request->file('report_file'));
            $p->reportAddWeekly((int)$student['id'], (int)$request->input('week_no'), trim((string)$request->input('report_text', '')), $path);
            return redirect()->route('student.records')->with('success', 'Weekly PDF report uploaded.');
        } catch (Throwable $e) {
            return redirect()->route('student.records')->with('error', $e->getMessage());
        }
    }

    private function studentPageData(PracticumService $p, string $title): array
    {
        $user = $this->requireRole($p, 'student');
        $student = $p->studentFindByUser((int)$user['id']);
        $enrollment = $student ? $p->enrollmentDetailsByStudent((int)$student['id']) : null;
        $requirements = $student ? $p->studentRequirements((int)$student['id']) : [];
        if ($student && $enrollment) {
            $enrollment['predeployment_status'] = $p->effectivePredeploymentStatusForStudent((int)$student['id'], $enrollment['predeployment_status'] ?? null, $requirements);
        }

        return [
            'title' => $title,
            'student' => $student,
            'enrollment' => $enrollment,
            'canSubmitReports' => $p->enrollmentAllowsReports($enrollment),
            'reportLockMessage' => $p->enrollmentReportLockMessage($enrollment),
            'dtrs' => $student ? $p->reportDtrByStudent((int)$student['id']) : [],
            'weeklyReports' => $student ? $p->reportWeeklyByStudent((int)$student['id']) : [],
            'hours' => $student ? $p->reportTotalHours((int)$student['id']) : 0,
            'requirements' => $requirements,
        ];
    }
}
