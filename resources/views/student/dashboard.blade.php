<?php $required = (float)($enrollment['required_hours'] ?? 0); $remaining = max(0, $required - $hours); $percent = $required > 0 ? min(100, round(($hours / $required) * 100, 1)) : 0; $ringOffset = 314 - (314 * $percent / 100); ?>
<?php
    $scheduleStart = $enrollment['official_start_date'] ?? $enrollment['start_date'] ?? null;
    $scheduleEnd = $enrollment['projected_end_date'] ?? $enrollment['end_date'] ?? null;
    $startFormatted = !empty($scheduleStart) ? date('M d, Y', strtotime($scheduleStart)) : 'Awaiting partner schedule';
    $endFormatted   = !empty($scheduleEnd)   ? date('M d, Y', strtotime($scheduleEnd))   : '';
?>
<div class="grid cards student-summary-grid">
    <div class="card metric metric-company-card">
        <div class="metric-icon">
            <svg viewBox="0 0 24 24"><path d="M4 20h16v-2h-1V4H9v4H5v10H4v2Zm3-2v-8h4v8H7Zm6 0V6h4v12h-4Zm-2-10H9V6h2v2Zm4 1h1v2h-1V9Zm0 4h1v2h-1v-2ZM9 12h2v2H9v-2Zm0 3h2v2H9v-2Z"/></svg>
        </div>
        <div class="metric-body">
            <span class="metric-label">Company</span>
            <strong class="metric-value metric-company-value"><?= e($enrollment['company_name'] ?? 'Not enrolled') ?></strong>
        </div>
    </div>
    <div class="card metric">
        <div class="metric-icon">
            <svg viewBox="0 0 24 24"><path d="M7 2h2v2h6V2h2v2h3v18H4V4h3V2Zm11 8H6v10h12V10Z"/></svg>
        </div>
        <div class="metric-body">
            <span class="metric-label">OJT Schedule</span>
            <strong class="metric-value metric-schedule"><?= e($startFormatted) ?></strong>
            <?php if ($endFormatted !== ''): ?>
                <span class="metric-schedule-to">to <?= e($endFormatted) ?></span>
            <?php else: ?>
                <span class="metric-schedule-to">Partner company will confirm your official start date.</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card metric">
        <div class="metric-icon">
            <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 5v5l4 2-.8 1.8L11 13V7h2Z"/></svg>
        </div>
        <div class="metric-body">
            <span class="metric-label">Remaining Hours</span>
            <strong class="metric-value"><?= number_format($remaining, 2) ?></strong>
        </div>
    </div>
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

@include('student.partials.predeployment_requirements')
