<?php $required = (float)($enrollment['required_hours'] ?? 0); $remaining = max(0, $required - $hours); $percent = $required > 0 ? min(100, round(($hours / $required) * 100, 1)) : 0; $ringOffset = 314 - (314 * $percent / 100); ?>
<section class="hero-banner compact student-hero">
    <div>
        <span class="eyebrow">Student Portal</span>
        <h2>Track your OJT progress, submit reports, and review deployment details.</h2>
        <p class="muted">Everything you need for practicum compliance is organized below in a cleaner, easier-to-read interface.</p>
    </div>
    <div class="hero-actions"><span class="hero-pill"><?= e($enrollment['company_name'] ?? 'Awaiting deployment') ?></span></div>
</section>
<div class="grid cards">
    <div class="card metric"><svg viewBox="0 0 24 24"><path d="M3 5h18v14H3V5Zm2 2v10h14V7H5Z"/></svg><div><strong><?= e($enrollment['company_name'] ?? 'Not enrolled') ?></strong><span>Company</span></div></div>
    <div class="card metric"><svg viewBox="0 0 24 24"><path d="M7 2h2v2h6V2h2v2h3v18H4V4h3V2Zm11 8H6v10h12V10Z"/></svg><div><strong><?= e(($enrollment['start_date'] ?? '-') . ' to ' . ($enrollment['end_date'] ?? '-')) ?></strong><span>OJT Schedule</span></div></div>
    <div class="card metric"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 5v5l4 2-.8 1.8L11 13V7h2Z"/></svg><div><strong><?= number_format($remaining, 2) ?></strong><span>Remaining Hours</span></div></div>
</div>

<section class="card progress-card ring-progress-card">
    <div class="progress-ring-wrap">
        <svg class="progress-ring" viewBox="0 0 120 120" aria-label="OJT progress">
            <circle class="ring-bg" cx="60" cy="60" r="50"></circle>
            <circle class="ring-value" cx="60" cy="60" r="50" style="stroke-dashoffset: <?= $ringOffset ?>"></circle>
        </svg>
        <div class="ring-label"><strong><?= $percent ?>%</strong><span>Complete</span></div>
    </div>
    <div>
        <div class="section-head"><div><h2>OJT Progress Tracker</h2><p class="muted"><?= number_format($hours, 2) ?> rendered hours out of <?= number_format($required, 2) ?> required hours</p></div><span class="badge <?= e($enrollment['status'] ?? 'pending') ?>"><?= e($enrollment['status'] ?? 'pending') ?></span></div>
        <p class="muted">Keep submitting daily time records until you reach the required hours. Your enrollment is automatically marked completed when the requirement is met.</p>
    </div>
</section>

<section class="card">
    <div class="section-head section-head-split">
        <div><h2>Pre-Deployment Requirements</h2><p class="muted">Upload all required documents, then submit them for coordinator review. If one file is rejected, only that file needs to be corrected.</p></div>
        <span class="badge <?= e($enrollment['predeployment_status'] ?? 'not_submitted') ?>"><?= e(str_replace('_', ' ', $enrollment['predeployment_status'] ?? 'not_submitted')) ?></span>
    </div>
    <?php
        $allRequirementsApproved = true;
        $allRequirementsUploaded = true;
        $hasRejectedRequirements = false;
        foreach ($requirements as $checkReq) {
            if (empty($checkReq['file_path'])) $allRequirementsUploaded = false;
            if (empty($checkReq['file_path']) || ($checkReq['status'] ?? '') !== 'approved') $allRequirementsApproved = false;
            if (!empty($checkReq['file_path']) && ($checkReq['status'] ?? '') === 'rejected') $hasRejectedRequirements = true;
        }
    ?>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Requirement</th><th>Notes</th><th>File</th><th>Status</th><th>Upload</th></tr></thead><tbody>
        <?php foreach ($requirements as $key => $req): ?>
            <?php
                $predeploymentStatus = $enrollment['predeployment_status'] ?? 'not_submitted';
                $requirementStatus = $req['status'] ?? 'pending';
                $hasRequirementFile = !empty($req['file_path']);
                $canUploadRequirement = !in_array($predeploymentStatus, ['approved','forwarded','accepted','orientation_scheduled','orientation_completed'], true)
                    && ($requirementStatus === 'rejected' || !$hasRequirementFile)
                    && !($predeploymentStatus === 'submitted' && $requirementStatus !== 'rejected');
                $uploadStatusLabel = match (true) {
                    $requirementStatus === 'approved' => 'Approved',
                    $requirementStatus === 'uploaded' && $hasRequirementFile => 'Awaiting review',
                    $requirementStatus === 'rejected' => 'Replace rejected file',
                    $predeploymentStatus === 'submitted' => 'Under review',
                    $hasRequirementFile => 'Already uploaded',
                    default => 'Ready to upload',
                };
            ?>
            <tr>
                <td><?= e($req['requirement_name']) ?></td>
                <td><?= e($req['notes'] ?? '') ?></td>
                <td><?= !empty($req['file_path']) ? '<a class="btn btn-small" target="_blank" href="' . e(asset($req['file_path'])) . '">View</a>' : '<span class="muted">Not uploaded</span>' ?></td>
                <td>
                    <span class="badge <?= e($requirementStatus) ?>"><?= e(str_replace('_', ' ', $requirementStatus)) ?></span>
                    <?php if (!empty($req['review_notes'])): ?><div class="muted" style="margin-top:6px;font-size:.8rem;line-height:1.4;"><?= e($req['review_notes']) ?></div><?php endif; ?>
                </td>
                <td>
                    <?php if ($canUploadRequirement): ?>
                    <form action="{{ route('student.requirements.upload') }}" method="post" enctype="multipart/form-data" class="inline" style="display:flex;gap:8px;align-items:center">
                        @csrf
                        <input type="hidden" name="requirement_key" value="<?= e($key) ?>">
                        <input required type="file" name="requirement_file" accept=".pdf,.jpg,.jpeg,.png">
                        <button class="btn btn-small" type="submit"><?= $predeploymentStatus === 'needs_revision' ? 'Replace File' : 'Upload' ?></button>
                    </form>
                    <?php else: ?><span class="muted"><?= e($uploadStatusLabel) ?></span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody></table></div>
    <?php if ($allRequirementsApproved): ?>
        <div class="status-callout success" style="margin-top:16px">
            <strong>All documents approved.</strong>
            <p class="muted">Submit for Review is locked because your coordinator already approved all required documents. Please wait for forwarding to the company partner.</p>
            <button class="btn btn-primary" type="button" disabled>Documents Already Approved</button>
        </div>
    <?php elseif ($hasRejectedRequirements): ?>
        <div class="status-callout warning" style="margin-top:16px">
            <strong>Revision required.</strong>
            <p class="muted">Only the rejected document is unlocked. Replace it first, then it will return to coordinator review automatically.</p>
            <button class="btn btn-primary" type="button" disabled>Fix Rejected Document</button>
        </div>
    <?php elseif (($enrollment['predeployment_status'] ?? 'not_submitted') === 'submitted'): ?>
        <div class="status-callout info" style="margin-top:16px">
            <strong>Documents under review.</strong>
            <p class="muted">You already submitted your requirements. The button is locked to prevent duplicate submissions.</p>
            <button class="btn btn-primary" type="button" disabled>Already Submitted</button>
        </div>
    <?php elseif (($enrollment['predeployment_status'] ?? 'not_submitted') === 'not_submitted' && $allRequirementsUploaded): ?>
        <form action="{{ route('student.requirements.submit') }}" method="post" style="margin-top:16px">
            @csrf
            <button class="btn btn-primary" type="submit">Submit for Review</button>
        </form>
    <?php else: ?>
        <div class="status-callout info" style="margin-top:16px">
            <strong>Upload all requirements first.</strong>
            <p class="muted">Submit for Review will unlock after all five required documents have been uploaded.</p>
            <button class="btn btn-primary" type="button" disabled>Submit for Review Locked</button>
        </div>
    <?php endif; ?>
</section>

<div class="grid two">
    <?php if (!($canSubmitReports ?? false)): ?>
    <section class="card locked-card"><h2>Submit Daily Time Record</h2><p class="muted"><?= e($reportLockMessage ?? 'DTR is locked until your OJT deployment starts.') ?></p><button class="btn btn-primary" type="button" disabled>Submit DTR Locked</button></section>
    <section class="card locked-card"><h2>Submit Weekly Narrative</h2><p class="muted"><?= e($reportLockMessage ?? 'Weekly reports are locked until your OJT deployment starts.') ?></p><button class="btn btn-primary" type="button" disabled>Weekly Report Locked</button></section>
    <?php else: ?>
    <section class="card"><h2>Submit Daily Time Record</h2><form action="{{ route('student.dtr.store') }}" method="post" class="form js-validate">@csrf<label>Date<input required type="date" name="work_date"></label><label>Time In<input required type="time" name="time_in"></label><label>Time Out<input required type="time" name="time_out"></label><label>Tasks Done<textarea required maxlength="500" name="tasks_done"></textarea></label><button class="btn btn-primary" type="submit"><span class="btn-text">Submit DTR</span><span class="spinner"></span></button></form></section>
    <section class="card">
        <h2>Submit Weekly Narrative</h2>
        <form action="{{ route('student.weekly_reports.store') }}" method="post" enctype="multipart/form-data" class="form js-validate">
            @csrf
            <label>Week Number<input required type="number" min="1" name="week_no"></label>
            <label>Narrative Text<textarea maxlength="1500" name="report_text"></textarea></label>
            <label>PDF Report<input type="file" name="report_file" accept=".pdf"></label>
            <button class="btn btn-primary" type="submit"><span class="btn-text">Submit Weekly Report</span><span class="spinner"></span></button>
        </form>
        <hr style="border:0;border-top:1px solid var(--border);margin:18px 0;">
        <h3>Upload Weekly PDF Only</h3>
        <p class="muted">Use this if you only need to attach a PDF report for a week.</p>
        <form action="{{ route('student.reports.upload') }}" method="post" enctype="multipart/form-data" class="form js-validate">
            @csrf
            <label>Week Number<input required type="number" min="1" name="week_no"></label>
            <label>Notes<textarea maxlength="1500" name="report_text" placeholder="Optional notes"></textarea></label>
            <label>PDF Report<input required type="file" name="report_file" accept=".pdf"></label>
            <button class="btn btn-primary" type="submit"><span class="btn-text">Upload PDF Report</span><span class="spinner"></span></button>
        </form>
    </section>
    <?php endif; ?>
</div>

<section class="card">
    <div class="section-head"><h2>Activity Timeline</h2><span class="muted">Daily time records</span></div>
    <div class="timeline">
        <?php if (!$dtrs): ?><p class="muted">No daily time records submitted yet.</p><?php endif; ?>
        <?php foreach ($dtrs as $d): ?>
            <article class="timeline-item" data-detail="<?= e($d['work_date'] . '|' . $d['time_in'] . ' - ' . $d['time_out'] . '|' . $d['hours'] . ' hours|' . $d['tasks_done']) ?>">
                <span class="timeline-dot"></span>
                <div class="timeline-card"><strong><?= e($d['work_date']) ?></strong><small><?= e($d['time_in']) ?> - <?= e($d['time_out']) ?> · <?= e($d['hours']) ?> hours</small><p><?= e($d['tasks_done']) ?></p></div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="card"><h2>My Documents</h2><p>COR: <?php if ($student && !empty($student['cor_file'])): ?><a class="btn btn-small" target="_blank" href="<?= e(asset($student['cor_file'])) ?>">View COR</a><?php else: ?><span class="muted">No COR uploaded</span><?php endif; ?></p><div class="table-wrap"><table class="data-table"><thead><tr><th>Week</th><th>Text</th><th>File</th></tr></thead><tbody><?php foreach ($weeklyReports as $r): ?><tr><td><?= (int)$r['week_no'] ?></td><td><?= e($r['report_text']) ?></td><td><?= $r['file_path'] ? '<a class="btn btn-small" target="_blank" href="' . e(asset($r['file_path'])) . '">View PDF</a>' : '-' ?></td></tr><?php endforeach; ?></tbody></table></div></section>
