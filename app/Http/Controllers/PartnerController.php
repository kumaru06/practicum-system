<?php

namespace App\Http\Controllers;

use App\Services\PracticumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function dashboard(Request $request, PracticumService $p): View
    {
        $user = $this->requireRole($p, 'partner');
        $company = $p->companyFindByUser((int)$user['id']);
        $selected = $request->query('enrollment') ? $p->enrollmentFind((int)$request->query('enrollment')) : null;
        if ($selected && $company && (int)$selected['company_id'] !== (int)$company['id']) $selected = null;
        return $this->renderNative($p, 'partner.dashboard', ['title' => 'Partner Company Dashboard', 'company' => $company, 'students' => $company ? $p->enrollmentsDeployedByCompany((int)$company['id']) : [], 'selected' => $selected, 'dtrs' => $selected ? $p->reportDtrByStudent((int)$selected['student_id']) : [], 'evaluation' => $selected ? $p->evaluationByEnrollment((int)$selected['id']) : null, 'requirements' => $selected ? $p->studentRequirements((int)$selected['student_id']) : []]);
    }

    public function acceptDeployment(Request $request, PracticumService $p): RedirectResponse
    {
        [$company, $enrollment] = $this->ownedEnrollment($request, $p);
        $p->enrollmentAcceptDeployment((int)$enrollment['id']);
        $student = $p->studentFind((int)$enrollment['student_id']);
        if ($student) {
            $p->notificationCreate((int)$student['user_id'], 'Deployment accepted', $company['name'] . ' accepted your deployment documents.', route('student.dashboard'));
            $p->notificationCreate((int)$student['coordinator_id'], 'Deployment accepted', $company['name'] . ' accepted ' . $student['name'] . "'s deployment.", route('coordinator.students'));
        }
        return redirect()->route('partner.dashboard', ['enrollment' => (int)$enrollment['id']])->with('success', 'Deployment accepted. You can now schedule orientation.');
    }

    public function sendOrientationEmail(Request $request, PracticumService $p): RedirectResponse
    {
        [$company, $enrollment] = $this->ownedEnrollment($request, $p);
        $notes = trim((string)$request->input('orientation_notes', ''));
        $student = $p->studentFind((int)$enrollment['student_id']);
        $p->emailSend($enrollment['student_email'], 'OJT Orientation Instructions', 'orientation_email', 'orientation_notice', ['student' => $enrollment, 'company' => $company, 'orientationDateTime' => '', 'notes' => $notes]);
        if (!empty($student['coordinator_email'])) $p->emailSend($student['coordinator_email'], 'OJT Orientation Instructions', 'orientation_email', 'orientation_notice', ['student' => $student, 'company' => $company, 'orientationDateTime' => '', 'notes' => $notes]);
        if ($student) {
            $p->notificationCreate((int)$student['user_id'], 'Orientation instructions sent', $company['name'] . ' sent OJT orientation instructions.', route('student.dashboard'));
            $p->notificationCreate((int)$student['coordinator_id'], 'Orientation instructions sent', $company['name'] . ' sent orientation instructions for ' . $student['name'] . '.', route('coordinator.students'));
        }
        return redirect()->route('partner.dashboard', ['enrollment' => (int)$enrollment['id']])->with('success', 'Orientation email sent to the student and coordinator.');
    }

    public function scheduleOrientation(Request $request, PracticumService $p): RedirectResponse
    {
        [$company, $enrollment] = $this->ownedEnrollment($request, $p);
        $orientationDateTime = (string)$request->input('orientation_datetime');
        $notes = trim((string)$request->input('orientation_notes', ''));
        $p->enrollmentScheduleOrientation((int)$enrollment['id'], $orientationDateTime, $notes);
        $student = $p->studentFind((int)$enrollment['student_id']);
        $p->emailSend($enrollment['student_email'], 'OJT Orientation Schedule', 'orientation_notice', 'orientation_notice', ['student' => $enrollment, 'company' => $company, 'orientationDateTime' => $orientationDateTime, 'notes' => $notes]);
        if (!empty($student['coordinator_email'])) $p->emailSend($student['coordinator_email'], 'OJT Orientation Schedule', 'orientation_notice', 'orientation_notice', ['student' => $student, 'company' => $company, 'orientationDateTime' => $orientationDateTime, 'notes' => $notes]);
        if ($student) {
            $p->notificationCreate((int)$student['user_id'], 'Orientation scheduled', $company['name'] . ' scheduled your OJT orientation.', route('student.dashboard'));
            $p->notificationCreate((int)$student['coordinator_id'], 'Orientation scheduled', $company['name'] . ' scheduled orientation for ' . $student['name'] . '.', route('coordinator.students'));
        }
        return redirect()->route('partner.dashboard', ['enrollment' => (int)$enrollment['id']])->with('success', 'Orientation scheduled and student notified.');
    }

    public function completeOrientation(Request $request, PracticumService $p): RedirectResponse
    {
        [$company, $enrollment] = $this->ownedEnrollment($request, $p);
        $officialStartDate = (string)$request->input('official_start_date');
        $projectedEndDate = trim((string)$request->input('projected_end_date', '')) ?: $p->projectedOjtEndDate($officialStartDate, (int)$enrollment['required_hours']);
        $p->enrollmentCompleteOrientation((int)$enrollment['id'], $officialStartDate, $projectedEndDate);
        $student = $p->studentFind((int)$enrollment['student_id']);
        $p->emailSend($enrollment['student_email'], 'Your OJT Has Officially Started', 'ojt_started', 'ojt_started', ['student' => $enrollment, 'company' => $company, 'officialStartDate' => $officialStartDate, 'projectedEndDate' => $projectedEndDate, 'requiredHours' => (int)$enrollment['required_hours']]);
        if (!empty($student['coordinator_email'])) $p->emailSend($student['coordinator_email'], 'Student OJT Has Officially Started', 'ojt_started', 'ojt_started', ['student' => $student, 'company' => $company, 'officialStartDate' => $officialStartDate, 'projectedEndDate' => $projectedEndDate, 'requiredHours' => (int)$enrollment['required_hours']]);
        if ($student) {
            $p->notificationCreate((int)$student['user_id'], 'OJT officially started', 'Your official OJT start date is ' . $officialStartDate . '.', route('student.dashboard'));
            $p->notificationCreate((int)$student['coordinator_id'], 'Student OJT started', $student['name'] . ' officially started OJT at ' . $company['name'] . '.', route('coordinator.students'));
        }
        return redirect()->route('partner.dashboard', ['enrollment' => (int)$enrollment['id']])->with('success', 'Orientation completed and official OJT dates saved.');
    }

    public function submitEvaluation(Request $request, PracticumService $p): RedirectResponse
    {
        [$company, $enrollment] = $this->ownedEnrollment($request, $p);
        $p->evaluationSubmit((int)$enrollment['id'], (int)$company['id'], (int)$request->input('rating'), trim((string)$request->input('comments')));
        return redirect()->route('partner.dashboard', ['enrollment' => (int)$enrollment['id']])->with('success', 'Final evaluation submitted.');
    }

    private function ownedEnrollment(Request $request, PracticumService $p): array
    {
        $user = $this->requireRole($p, 'partner');
        $company = $p->companyFindByUser((int)$user['id']);
        $enrollment = $p->enrollmentFind((int)$request->input('enrollment_id'));
        if (!$company || !$enrollment || (int)$enrollment['company_id'] !== (int)$company['id']) abort(403, 'Forbidden');
        return [$company, $enrollment];
    }
}
