<section class="card">
    <div class="section-head section-head-split">
        <div><h2>Account Settings</h2><p class="muted">Manage your student profile details and account security from this section.</p></div>
        <span class="badge active">student</span>
    </div>
    <div class="grid two">
        <div class="card">
            <div class="card-head"><h2>Profile Information</h2><p class="muted">Review your current student information.</p></div>
            <div class="detail-row"><span>Student Number</span><strong><?= e($student['student_no'] ?? '—') ?></strong></div>
            <div class="detail-row"><span>Course</span><strong><?= e($student['course'] ?? '—') ?></strong></div>
            <div class="detail-row"><span>Year & Section</span><strong><?= e(trim(($student['year_level'] ?? '—') . ' ' . ($student['section'] ?? ''))) ?></strong></div>
            <div class="detail-row"><span>Contact Number</span><strong><?= e($student['contact_number'] ?? '—') ?></strong></div>
            <div class="detail-row"><span>Company</span><strong><?= e($enrollment['company_name'] ?? 'Awaiting deployment') ?></strong></div>
        </div>
        <div class="card">
            <div class="card-head"><h2>Account Actions</h2><p class="muted">Open a focused page to update profile details or change your password.</p></div>
            <div class="grid" style="gap:12px;">
                <a class="btn btn-primary" href="{{ route('student.profile') }}">Edit Profile</a>
                <a class="btn btn-small" href="{{ route('student.password.edit') }}">Change Password</a>
            </div>
            <div class="detail-row" style="margin-top:16px;"><span>Enrollment Status</span><strong><?= e(ucwords(str_replace('_', ' ', $enrollment['status'] ?? 'pending'))) ?></strong></div>
            <div class="detail-row"><span>Pre-deployment</span><strong><?= e(ucwords(str_replace('_', ' ', $enrollment['predeployment_status'] ?? 'not submitted'))) ?></strong></div>
        </div>
    </div>
</section>
