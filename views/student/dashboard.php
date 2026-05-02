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

<div class="grid two">
    <section class="card"><h2>Submit Daily Time Record</h2><form method="post" class="form js-validate"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="student_add_dtr"><label>Date<input required type="date" name="work_date"></label><label>Time In<input required type="time" name="time_in"></label><label>Time Out<input required type="time" name="time_out"></label><label>Tasks Done<textarea required maxlength="500" name="tasks_done"></textarea></label><button class="btn btn-primary" type="submit"><span class="btn-text">Submit DTR</span><span class="spinner"></span></button></form></section>
    <section class="card"><h2>Submit Weekly Narrative</h2><form method="post" enctype="multipart/form-data" class="form js-validate"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="student_add_weekly"><label>Week Number<input required type="number" min="1" name="week_no"></label><label>Narrative Text<textarea maxlength="1500" name="report_text"></textarea></label><label>PDF Report<input type="file" name="report_file" accept=".pdf"></label><button class="btn btn-primary" type="submit"><span class="btn-text">Submit Weekly Report</span><span class="spinner"></span></button></form></section>
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

<section class="card"><h2>My Documents</h2><p>COR: <?php if ($student): ?><a class="btn btn-small" target="_blank" href="<?= e($student['cor_file']) ?>">View COR</a><?php endif; ?></p><div class="table-wrap"><table class="data-table"><thead><tr><th>Week</th><th>Text</th><th>File</th></tr></thead><tbody><?php foreach ($weeklyReports as $r): ?><tr><td><?= (int)$r['week_no'] ?></td><td><?= e($r['report_text']) ?></td><td><?= $r['file_path'] ? '<a class="btn btn-small" target="_blank" href="' . e($r['file_path']) . '">View PDF</a>' : '-' ?></td></tr><?php endforeach; ?></tbody></table></div></section>
