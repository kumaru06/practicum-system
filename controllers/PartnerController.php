<?php
class PartnerController extends BaseController
{
    public function dashboard(): void
    {
        require_role('partner');
        $company = (new Company($this->db))->findByUser(current_user()['id']);
        $enroll = new Enrollment($this->db);
        $students = $company ? $enroll->deployedByCompany((int)$company['id']) : [];
        $selected = isset($_GET['enrollment']) ? $enroll->find((int)$_GET['enrollment']) : null;
        if ($selected && $company && (int)$selected['company_id'] !== (int)$company['id']) {
            $selected = null; // deny cross-company access
        }
        $dtrs = [];
        $evaluation = null;
        if ($selected) {
            $dtrs = (new Report($this->db))->dtrByStudent((int)$selected['student_id']);
            $evaluation = (new Evaluation($this->db))->byEnrollment((int)$selected['id']);
        }
        $this->render('partner/dashboard', [
            'title' => 'Partner Company Dashboard',
            'company' => $company,
            'students' => $students,
            'selected' => $selected,
            'dtrs' => $dtrs,
            'evaluation' => $evaluation,
            'requirements' => $selected ? (new Student($this->db))->requirements((int)$selected['student_id']) : [],
        ]);
    }

    public function acceptDeployment(): void
    {
        require_role('partner');
        $p = $this->post();
        $company = (new Company($this->db))->findByUser(current_user()['id']);
        $enrollment = (new Enrollment($this->db))->find((int)$p['enrollment_id']);
        if (!$company || !$enrollment || (int)$enrollment['company_id'] !== (int)$company['id']) {
            http_response_code(403);
            exit('Forbidden');
        }
        (new Enrollment($this->db))->acceptDeployment((int)$enrollment['id']);
        $studentDetails = (new Student($this->db))->find((int)$enrollment['student_id']);
        if ($studentDetails) {
            $notifications = new Notification($this->db);
            $notifications->create((int)$studentDetails['user_id'], 'Deployment accepted', $company['name'] . ' accepted your deployment documents.', 'index.php?r=student');
            $notifications->create((int)$studentDetails['coordinator_id'], 'Deployment accepted', $company['name'] . ' accepted ' . $studentDetails['name'] . '\'s deployment.', 'index.php?r=coordinator_students');
        }
        flash('success', 'Deployment accepted. You can now schedule orientation.');
        redirect('index.php?r=partner&enrollment=' . (int)$enrollment['id']);
    }

    public function sendOrientationEmail(): void
    {
        require_role('partner');
        $p = $this->post();
        $company = (new Company($this->db))->findByUser(current_user()['id']);
        $enrollment = (new Enrollment($this->db))->find((int)$p['enrollment_id']);
        if (!$company || !$enrollment || (int)$enrollment['company_id'] !== (int)$company['id']) {
            http_response_code(403);
            exit('Forbidden');
        }
        $notes = trim($p['orientation_notes'] ?? '');
        $email = new Email($this->db);
        $studentDetails = (new Student($this->db))->find((int)$enrollment['student_id']);
        $email->send($enrollment['student_email'], 'OJT Orientation Instructions', 'orientation_email', 'orientation_notice', [
            'student' => $enrollment,
            'company' => $company,
            'orientationDateTime' => '',
            'notes' => $notes,
        ]);
        if (!empty($studentDetails['coordinator_email'])) {
            $email->send($studentDetails['coordinator_email'], 'OJT Orientation Instructions', 'orientation_email', 'orientation_notice', [
                'student' => $studentDetails,
                'company' => $company,
                'orientationDateTime' => '',
                'notes' => $notes,
            ]);
        }
        if ($studentDetails) {
            $notifications = new Notification($this->db);
            $notifications->create((int)$studentDetails['user_id'], 'Orientation instructions sent', $company['name'] . ' sent OJT orientation instructions.', 'index.php?r=student');
            $notifications->create((int)$studentDetails['coordinator_id'], 'Orientation instructions sent', $company['name'] . ' sent orientation instructions for ' . $studentDetails['name'] . '.', 'index.php?r=coordinator_students');
        }
        flash('success', 'Orientation email sent to the student and coordinator.');
        redirect('index.php?r=partner&enrollment=' . (int)$enrollment['id']);
    }

    public function scheduleOrientation(): void
    {
        require_role('partner');
        $p = $this->post();
        $company = (new Company($this->db))->findByUser(current_user()['id']);
        $enrollment = (new Enrollment($this->db))->find((int)$p['enrollment_id']);
        if (!$company || !$enrollment || (int)$enrollment['company_id'] !== (int)$company['id']) {
            http_response_code(403);
            exit('Forbidden');
        }
        (new Enrollment($this->db))->scheduleOrientation((int)$enrollment['id'], $p['orientation_datetime'], trim($p['orientation_notes'] ?? ''));
        $email = new Email($this->db);
        $studentDetails = (new Student($this->db))->find((int)$enrollment['student_id']);
        $email->send($enrollment['student_email'], 'OJT Orientation Schedule', 'orientation_notice', 'orientation_notice', [
            'student' => $enrollment,
            'company' => $company,
            'orientationDateTime' => $p['orientation_datetime'],
            'notes' => trim($p['orientation_notes'] ?? ''),
        ]);
        if (!empty($studentDetails['coordinator_email'])) {
            $email->send($studentDetails['coordinator_email'], 'OJT Orientation Schedule', 'orientation_notice', 'orientation_notice', [
                'student' => $studentDetails,
                'company' => $company,
                'orientationDateTime' => $p['orientation_datetime'],
                'notes' => trim($p['orientation_notes'] ?? ''),
            ]);
        }
        if ($studentDetails) {
            $notifications = new Notification($this->db);
            $notifications->create((int)$studentDetails['user_id'], 'Orientation scheduled', $company['name'] . ' scheduled your OJT orientation.', 'index.php?r=student');
            $notifications->create((int)$studentDetails['coordinator_id'], 'Orientation scheduled', $company['name'] . ' scheduled orientation for ' . $studentDetails['name'] . '.', 'index.php?r=coordinator_students');
        }
        flash('success', 'Orientation scheduled and student notified.');
        redirect('index.php?r=partner&enrollment=' . (int)$enrollment['id']);
    }

    public function completeOrientation(): void
    {
        require_role('partner');
        $p = $this->post();
        $company = (new Company($this->db))->findByUser(current_user()['id']);
        $enrollment = (new Enrollment($this->db))->find((int)$p['enrollment_id']);
        if (!$company || !$enrollment || (int)$enrollment['company_id'] !== (int)$company['id']) {
            http_response_code(403);
            exit('Forbidden');
        }
        $projectedEndDate = trim($p['projected_end_date'] ?? '') ?: projected_ojt_end_date($p['official_start_date'], (int)$enrollment['required_hours']);
        (new Enrollment($this->db))->completeOrientation((int)$enrollment['id'], $p['official_start_date'], $projectedEndDate);
        $email = new Email($this->db);
        $studentDetails = (new Student($this->db))->find((int)$enrollment['student_id']);
        $email->send($enrollment['student_email'], 'Your OJT Has Officially Started', 'ojt_started', 'ojt_started', [
            'student' => $enrollment,
            'company' => $company,
            'officialStartDate' => $p['official_start_date'],
            'projectedEndDate' => $projectedEndDate,
            'requiredHours' => (int)$enrollment['required_hours'],
        ]);
        if (!empty($studentDetails['coordinator_email'])) {
            $email->send($studentDetails['coordinator_email'], 'Student OJT Has Officially Started', 'ojt_started', 'ojt_started', [
                'student' => $studentDetails,
                'company' => $company,
                'officialStartDate' => $p['official_start_date'],
                'projectedEndDate' => $projectedEndDate,
                'requiredHours' => (int)$enrollment['required_hours'],
            ]);
        }
        if ($studentDetails) {
            $notifications = new Notification($this->db);
            $notifications->create((int)$studentDetails['user_id'], 'OJT officially started', 'Your official OJT start date is ' . $p['official_start_date'] . '.', 'index.php?r=student');
            $notifications->create((int)$studentDetails['coordinator_id'], 'Student OJT started', $studentDetails['name'] . ' officially started OJT at ' . $company['name'] . '.', 'index.php?r=coordinator_students');
        }
        flash('success', 'Orientation completed and official OJT dates saved.');
        redirect('index.php?r=partner&enrollment=' . (int)$enrollment['id']);
    }

    public function submitEvaluation(): void
    {
        require_role('partner');
        $p = $this->post();
        $company = (new Company($this->db))->findByUser(current_user()['id']);
        $enroll = new Enrollment($this->db);
        $enrollment = $enroll->find((int)$p['enrollment_id']);
        if (!$enrollment || !$company || (int)$enrollment['company_id'] !== (int)$company['id']) {
            http_response_code(403);
            exit('Forbidden');
        }
        (new Evaluation($this->db))->submit((int)$p['enrollment_id'], (int)$company['id'], (int)$p['rating'], trim($p['comments']));
        flash('success', 'Final evaluation submitted.');
        redirect('index.php?r=partner&enrollment=' . (int)$p['enrollment_id']);
    }
}
