<?php

namespace App\Http\Controllers;

use App\Services\PracticumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CoordinatorController extends Controller
{
    public function dashboard(PracticumService $p): View
    {
        $user = $this->requireRole($p, 'coordinator');
        $id = (int)$user['id'];
        return $this->renderNative($p, 'coordinator.dashboard', ['title' => 'Coordinator Dashboard', 'stats' => ['students' => $p->studentCountByCoordinator($id), 'enrolled' => $p->enrollmentCountByCoordinator($id, 'active'), 'completed' => $p->enrollmentCountByCoordinator($id, 'completed'), 'pending' => $p->enrollmentCountByCoordinator($id, 'pending')], 'charts' => ['statusDistribution' => $p->enrollmentStatusDistributionByCoordinator($id), 'completionRates' => $p->enrollmentCompletionRatesByCourseByCoordinator($id), 'monthlyTrends' => $p->enrollmentMonthlyTrendsByCoordinator($id)]]);
    }

    public function manage(PracticumService $p): View
    {
        $user = $this->requireRole($p, 'coordinator');
        return $this->renderNative($p, 'coordinator.manage', ['title' => 'Student Enrollment', 'students' => $p->studentsAllByCoordinator((int)$user['id']), 'companies' => $p->companiesAll(), 'programs' => $p->programsAll(true)]);
    }

    public function students(PracticumService $p): View
    {
        $user = $this->requireRole($p, 'coordinator');
        $students = $p->studentsAllByCoordinator((int)$user['id']);
        $requirementsByStudent = [];
        foreach ($students as &$student) {
            $studentId = (int)$student['id'];
            $requirementsByStudent[$studentId] = $p->studentRequirements($studentId);
            $student['predeployment_status'] = $p->effectivePredeploymentStatusForStudent($studentId, $student['predeployment_status'] ?? null, $requirementsByStudent[$studentId]);
        }
        unset($student);
        return $this->renderNative($p, 'coordinator.my_students', ['title' => 'My Students', 'students' => $students, 'requirementsByStudent' => $requirementsByStudent, 'evaluations' => $p->evaluationsByCoordinator((int)$user['id'])]);
    }

    public function evaluations(PracticumService $p): View
    {
        $user = $this->requireRole($p, 'coordinator');
        return $this->renderNative($p, 'coordinator.evaluations', ['title' => 'Evaluations', 'evaluations' => $p->evaluationsByCoordinator((int)$user['id'])]);
    }

    public function createStudent(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'coordinator');
        try {
            $program = $p->programFind((int)$request->input('program_id'));
            if (!$program) throw new \RuntimeException('Select a valid program/course.');
            $password = $p->randomPassword();
            $corPath = $p->uploadDocument($request->file('cor_file'), 'cor', true);
            $userId = $p->userCreate(trim((string)$request->input('full_name')), trim((string)$request->input('email')), $password, 'student', (int)$user['id'], 0);
            $p->studentCreate($userId, trim((string)$request->input('student_no')), $program['name'], trim((string)$request->input('year_level')), (string)$corPath, (int)$user['id'], (int)$program['id'], trim((string)$request->input('section', '')));
            return redirect()->route('coordinator.manage')->with('success', 'Student account created. Credentials can be sent after enrollment.');
        } catch (Throwable $e) {
            return redirect()->route('coordinator.manage')->with('error', $e->getMessage());
        }
    }

    public function enrollStudent(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'coordinator');
        try {
            $student = $p->studentFind((int)$request->input('student_id'));
            if (!$student || (int)$student['coordinator_id'] !== (int)$user['id']) throw new \RuntimeException('Student does not belong to your coordination.');
            $program = !empty($student['program_id']) ? $p->programFind((int)$student['program_id']) : null;
            if (!$program) throw new \RuntimeException('Student has no valid program/course assigned.');
            $companyId = (int)$request->input('company_id');
            if (!$p->companyAcceptsProgram($companyId, (int)$program['id'])) throw new \RuntimeException('Selected partner company is not available.');
            $requiredHours = (int)$program['required_hours'];
            $academicTerm = trim((string)$request->input('academic_term', ''));
            $termStartDate = (string)$request->input('term_start_date', '');
            $termEndDate = (string)$request->input('term_end_date', '');
            $p->enrollmentCreate((int)$student['id'], $companyId, null, null, $requiredHours, $academicTerm, $termStartDate, $termEndDate);
            $company = $p->companyFind($companyId);
            $tempPassword = $p->randomPassword();
            $p->userUpdatePassword((int)$student['user_id'], $tempPassword, 0);
            $data = ['student' => $student, 'company' => $company, 'academicTerm' => $academicTerm, 'termStartDate' => $termStartDate, 'termEndDate' => $termEndDate, 'requiredHours' => $requiredHours, 'password' => $tempPassword, 'coordinator' => $user];
            $p->emailSend($student['email'], 'You are now enrolled in OJT – AMA Computer College', 'student_enrollment', 'student_enrollment', $data);
            return redirect()->route('coordinator.manage')->with('success', 'Student enrolled and credentials email was processed. Partner deployment email will be sent after approved documents are forwarded.');
        } catch (Throwable $e) {
            return redirect()->route('coordinator.manage')->with('error', $e->getMessage());
        }
    }

    public function reviewRequirement(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'coordinator');
        try {
            $student = $p->studentFind((int)$request->input('student_id'));
            if (!$student || (int)$student['coordinator_id'] !== (int)$user['id']) throw new \RuntimeException('Student does not belong to your coordination.');
            $status = trim((string)$request->input('status', ''));
            $p->studentReviewRequirement((int)$student['id'], trim((string)$request->input('requirement_key', '')), $status, trim((string)$request->input('notes', '')));
            if ($status === 'rejected') {
                $p->enrollmentSetPredeploymentStatus((int)$student['id'], 'needs_revision');
                $p->notificationCreate((int)$student['user_id'], 'Requirement needs revision', 'One of your pre-deployment requirements was rejected. Only the rejected file needs to be corrected and re-uploaded.', route('student.dashboard'));
            } elseif ($p->studentHasApprovedRequirements((int)$student['id'])) {
                $p->enrollmentSetPredeploymentStatus((int)$student['id'], 'approved');
                $p->notificationCreate((int)$student['user_id'], 'Requirements approved', 'All of your pre-deployment requirements have been approved by your coordinator.', route('student.dashboard'));
            } else {
                $p->enrollmentSetPredeploymentStatus((int)$student['id'], $p->studentHasRejectedRequirements((int)$student['id']) ? 'needs_revision' : 'submitted');
            }
            return redirect()->route('coordinator.students')->with('success', 'Requirement review saved.');
        } catch (Throwable $e) {
            return redirect()->route('coordinator.students')->with('error', $e->getMessage());
        }
    }

    public function resetStudentPassword(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'coordinator');
        try {
            $student = $p->studentFind((int)$request->input('student_id'));
            if (!$student || (int)$student['coordinator_id'] !== (int)$user['id']) throw new \RuntimeException('Invalid student.');
            $password = $p->randomPassword();
            $p->userUpdatePassword((int)$student['user_id'], $password, 0);
            $sent = $p->emailSend($student['email'], 'Your AMA OJT password has been reset', 'password_reset', 'password_reset', ['student' => $student, 'password' => $password, 'coordinator' => $user]);
            return redirect()->route('coordinator.students')->with($sent ? 'success' : 'error', $sent ? 'Student password reset and emailed.' : 'Password was reset, but email sending failed. Check Email Logs.');
        } catch (Throwable $e) {
            return redirect()->route('coordinator.students')->with('error', 'Password reset failed: ' . $e->getMessage());
        }
    }

    public function forwardDeployment(Request $request, PracticumService $p): RedirectResponse
    {
        $user = $this->requireRole($p, 'coordinator');
        try {
            $enrollment = $p->enrollmentFind((int)$request->input('enrollment_id'));
            if (!$enrollment) throw new \RuntimeException('Enrollment not found.');
            $student = $p->studentFind((int)$enrollment['student_id']);
            if (!$student || (int)$student['coordinator_id'] !== (int)$user['id']) throw new \RuntimeException('Student does not belong to your coordination.');
            if (!$p->studentHasApprovedRequirements((int)$student['id'])) throw new \RuntimeException('Approve all five requirements before forwarding deployment documents.');
            $company = $p->companyFind((int)$enrollment['company_id']);
            if (!$company) throw new \RuntimeException('Partner company not found.');
            $endorsement = $request->file('endorsement_file')
                ? $p->uploadDocument($request->file('endorsement_file'), 'endorsements')
                : $p->generateEndorsementLetter($student, $company, $user, $enrollment);
            $p->enrollmentApproveAndForward((int)$enrollment['id'], (string)$endorsement);
            if ($company) {
                $attachments = array_map(static fn ($path) => ['path' => $path], $p->studentRequirementFilePaths((int)$student['id']));
                $attachments[] = ['path' => $endorsement, 'name' => 'Endorsement Letter.' . pathinfo((string)$endorsement, PATHINFO_EXTENSION)];
                $p->emailSend($company['contact_email'], 'Student Deployment Documents Forwarded', 'deployment_forwarded', 'company_deployment', ['student' => $student, 'company' => $company, 'academicTerm' => $enrollment['academic_term'], 'termStartDate' => $enrollment['term_start_date'], 'termEndDate' => $enrollment['term_end_date'], 'requiredHours' => (int)$enrollment['required_hours'], 'coordinator' => $user], $attachments);
                $p->notificationCreate((int)$company['user_id'], 'Student deployment forwarded', $student['name'] . ' has been forwarded to your company for review.', route('partner.dashboard', ['enrollment' => (int)$enrollment['id']]));
            }
            return redirect()->route('coordinator.students')->with('success', 'Documents approved and forwarded to partner company.');
        } catch (Throwable $e) {
            return redirect()->route('coordinator.students')->with('error', $e->getMessage());
        }
    }
}
