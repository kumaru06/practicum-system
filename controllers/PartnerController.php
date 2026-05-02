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
        ]);
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
