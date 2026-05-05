<?php $required = (float)($enrollment['required_hours'] ?? 0); $remaining = max(0, $required - $hours); $percent = $required > 0 ? min(100, round(($hours / $required) * 100, 1)) : 0; $ringOffset = 314 - (314 * $percent / 100); ?>
<?php
    $scheduleStart = $enrollment['official_start_date'] ?? $enrollment['start_date'] ?? null;
    $scheduleEnd = $enrollment['projected_end_date'] ?? $enrollment['end_date'] ?? null;
    $startFormatted = !empty($scheduleStart) ? date('M d, Y', strtotime($scheduleStart)) : 'Awaiting partner schedule';
    $endFormatted   = !empty($scheduleEnd)   ? date('M d, Y', strtotime($scheduleEnd))   : '';
    $studentName = trim((string)($student['name'] ?? 'Student'));
    $firstName = $studentName !== '' ? explode(' ', $studentName)[0] : 'Student';
    $hourNow = (int)date('G');
    $greeting = $hourNow < 12 ? 'Good morning' : ($hourNow < 18 ? 'Good afternoon' : 'Good evening');
    $todayLabel = date('l, F j, Y');
    $statusLabel = ucwords(str_replace('_', ' ', $enrollment['status'] ?? 'pending'));
    $predeploymentStatus = $enrollment['predeployment_status'] ?? 'not_submitted';
    $predeploymentLabel = ucwords(str_replace('_', ' ', $predeploymentStatus));
    $percentLabel = rtrim(rtrim(number_format($percent, 1), '0'), '.');

    $totalRequirements = count($requirements);
    $approvedRequirements = 0;
    $uploadedRequirements = 0;
    foreach ($requirements as $req) {
        if (!empty($req['file_path'])) $uploadedRequirements++;
        if (($req['status'] ?? '') === 'approved') $approvedRequirements++;
    }
    $profilePercent = !empty($student['profile_completed']) ? 100 : 65;
    $documentsPercent = $totalRequirements > 0 ? round(($approvedRequirements / $totalRequirements) * 100) : 0;
    $hoursPercent = (int)round($percent);

    $latestDtr = null;
    foreach ($dtrs as $entry) {
        if (!$latestDtr || strtotime((string)$entry['work_date']) > strtotime((string)$latestDtr['work_date'])) {
            $latestDtr = $entry;
        }
    }

    $latestWeekly = null;
    foreach ($weeklyReports as $entry) {
        if (!$latestWeekly || (int)($entry['week_no'] ?? 0) > (int)($latestWeekly['week_no'] ?? 0)) {
            $latestWeekly = $entry;
        }
    }

    $submittedToday = false;
    $todayKey = date('Y-m-d');
    foreach ($dtrs as $entry) {
        if (($entry['work_date'] ?? '') === $todayKey) {
            $submittedToday = true;
            break;
        }
    }

    $alertTone = 'info';
    $alertTitle = 'Stay on track';
    $alertMessage = 'Review your latest progress and continue submitting your practicum requirements on time.';
    $alertActionLabel = 'Open Documents';
    $alertActionRoute = route('student.documents');

    if (empty($enrollment)) {
        $alertTone = 'warning';
        $alertTitle = 'Waiting for enrollment';
        $alertMessage = 'Your coordinator must enroll you in OJT before documents and reports fully unlock.';
        $alertActionLabel = 'Open Settings';
        $alertActionRoute = route('student.settings');
    } elseif (in_array($predeploymentStatus, ['not_submitted', 'needs_revision'], true)) {
        $alertTone = 'warning';
        $alertTitle = $predeploymentStatus === 'needs_revision' ? 'Revision required' : 'Complete your requirements';
        $alertMessage = $predeploymentStatus === 'needs_revision'
            ? 'One or more uploaded files were rejected. Replace the flagged document to continue deployment processing.'
            : 'Upload and complete all pre-deployment requirements so your coordinator can review them.';
        $alertActionLabel = 'Review Documents';
        $alertActionRoute = route('student.documents');
    } elseif ($predeploymentStatus === 'submitted') {
        $alertTone = 'info';
        $alertTitle = 'Documents under review';
        $alertMessage = 'Your pre-deployment requirements are already with your coordinator. Check back here for status updates.';
        $alertActionLabel = 'View Timeline';
        $alertActionRoute = route('student.timeline');
    } elseif (!($canSubmitReports ?? false)) {
        $alertTone = 'info';
        $alertTitle = 'Reports are still locked';
        $alertMessage = $reportLockMessage ?? 'Your partner company still needs to finalize your orientation or official OJT start date.';
        $alertActionLabel = 'Open Documents';
        $alertActionRoute = route('student.documents');
    } elseif (!$submittedToday) {
        $alertTone = 'warning';
        $alertTitle = 'Today\'s DTR is still pending';
        $alertMessage = 'You have not submitted a daily time record for today yet. Logging it now keeps your hours accurate.';
        $alertActionLabel = 'Submit DTR';
        $alertActionRoute = route('student.records');
    } elseif (empty($weeklyReports)) {
        $alertTone = 'info';
        $alertTitle = 'Upload your first weekly report';
        $alertMessage = 'Your daily records are active. Don\'t forget to upload your PDF weekly report as part of your practicum requirements.';
        $alertActionLabel = 'Open Submit Record';
        $alertActionRoute = route('student.records');
    } else {
        $alertTone = 'success';
        $alertTitle = 'You\'re doing great';
        $alertMessage = 'Your practicum progress is moving smoothly. Keep up your DTRs and weekly submissions to stay on pace.';
        $alertActionLabel = 'View Activity';
        $alertActionRoute = route('student.timeline');
    }
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

<div class="student-dash-hero-grid">
    <section class="card student-welcome-card">
        <div class="student-welcome-copy">
            <span class="student-welcome-eyebrow"><?= e($greeting) ?></span>
            <h2 class="student-welcome-title">Welcome back, <?= e($firstName) ?>.</h2>
            <p class="student-welcome-text">Here is your practicum snapshot for <?= e($todayLabel) ?>. Track your schedule, monitor your progress, and stay ready for the next requirement.</p>
            <div class="student-welcome-chips">
                <span class="student-welcome-chip"><?= e($statusLabel) ?></span>
                <span class="student-welcome-chip"><?= e($predeploymentLabel) ?></span>
                <span class="student-welcome-chip"><?= number_format($remaining, 2) ?> hours left</span>
            </div>
            <div class="student-dashboard-quick-actions">
                <a class="student-dashboard-quick-action" href="{{ route('student.records') }}">
                    <span class="student-dashboard-quick-icon"><svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm0 16H5V9h14v10Zm-9-8H7v3h3v3h3v-3h3v-3h-3V8h-3v3Z"/></svg></span>
                    <span>Submit DTR</span>
                </a>
                <a class="student-dashboard-quick-action" href="{{ route('student.documents') }}">
                    <span class="student-dashboard-quick-icon"><svg viewBox="0 0 24 24"><path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm7 1.5V8h4.5L14 3.5ZM9 12h6v2H9v-2Zm0 4h6v2H9v-2Z"/></svg></span>
                    <span>Documents</span>
                </a>
                <a class="student-dashboard-quick-action" href="{{ route('student.timeline') }}">
                    <span class="student-dashboard-quick-icon"><svg viewBox="0 0 24 24"><path d="M7 3a2 2 0 0 1 2 2v1h6V5a2 2 0 1 1 4 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm0 5v11h10V8H7Zm2 2h6v2H9v-2Zm0 4h4v2H9v-2Z"/></svg></span>
                    <span>Timeline</span>
                </a>
                <a class="student-dashboard-quick-action" href="{{ route('student.settings') }}">
                    <span class="student-dashboard-quick-icon"><svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.03 7.03 0 0 0-1.63-.94l-.36-2.54A.5.5 0 0 0 13.9 2h-3.8a.5.5 0 0 0-.49.42l-.36 2.54c-.58.23-1.12.54-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.31 8.48a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .6.22l2.39-.96c.51.4 1.05.71 1.63.94l.36 2.54a.5.5 0 0 0 .49.42h3.8a.5.5 0 0 0 .49-.42l.36-2.54c.58-.23 1.12-.54 1.63-.94l2.39.96a.5.5 0 0 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z"/></svg></span>
                    <span>Settings</span>
                </a>
            </div>
        </div>
        <div class="student-welcome-accent">
            <div class="student-welcome-orb">
                <strong><?= e($percentLabel) ?>%</strong>
                <span>Overall Progress</span>
            </div>
        </div>
    </section>

    <section class="card student-alert-card tone-<?= e($alertTone) ?>">
        <div class="student-alert-top">
            <span class="student-alert-icon">
                <?php if ($alertTone === 'success'): ?>
                    <svg viewBox="0 0 24 24"><path d="M9 16.17 4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17Z"/></svg>
                <?php elseif ($alertTone === 'warning'): ?>
                    <svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21Zm12-3h-2v-2h2v2Zm0-4h-2v-4h2v4Z"/></svg>
                <?php else: ?>
                    <svg viewBox="0 0 24 24"><path d="M11 17h2v-6h-2v6Zm0-8h2V7h-2v2Zm1 13C6.48 22 2 17.52 2 12S6.48 2 12 2s10 4.48 10 10-4.48 10-10 10Z"/></svg>
                <?php endif; ?>
            </span>
            <span class="student-alert-label">Smart Alert</span>
        </div>
        <h3 class="student-alert-title"><?= e($alertTitle) ?></h3>
        <p class="student-alert-text"><?= e($alertMessage) ?></p>
        <div class="student-alert-footer">
            <a class="btn btn-small" href="<?= e($alertActionRoute) ?>"><?= e($alertActionLabel) ?></a>
        </div>
    </section>
</div>

<div class="student-dash-bottom-grid">
    <section class="card student-snapshot-card">
        <div class="section-head section-head-split">
            <div>
                <h2>Mini Recent Snapshot</h2>
                <p class="muted">A quick look at your latest practicum activity.</p>
            </div>
            <a class="btn btn-small" href="{{ route('student.timeline') }}">Open Timeline</a>
        </div>
        <div class="student-snapshot-list">
            <article class="student-snapshot-item">
                <span class="student-snapshot-label">Last DTR Submission</span>
                <strong><?= e($latestDtr['work_date'] ?? 'No DTR submitted yet') ?></strong>
                <small><?= !empty($latestDtr) ? e(($latestDtr['time_in'] ?? '') . ' - ' . ($latestDtr['time_out'] ?? '') . ' • ' . ($latestDtr['hours'] ?? '0') . ' hrs') : 'Start logging your daily attendance once your OJT begins.' ?></small>
            </article>
            <article class="student-snapshot-item">
                <span class="student-snapshot-label">Latest Weekly Report</span>
                <strong><?= !empty($latestWeekly) ? 'Week ' . (int)$latestWeekly['week_no'] : 'No weekly report yet' ?></strong>
                <small><?= !empty($latestWeekly) ? 'Your latest uploaded PDF report is already on file.' : 'Submit your first weekly report from the Submit Record page.' ?></small>
            </article>
            <article class="student-snapshot-item">
                <span class="student-snapshot-label">Document Review</span>
                <strong><?= e($approvedRequirements . ' of ' . $totalRequirements) ?> approved</strong>
                <small><?= e($predeploymentLabel) ?><?= $uploadedRequirements < $totalRequirements ? ' • ' . ($totalRequirements - $uploadedRequirements) . ' still missing' : '' ?></small>
            </article>
        </div>
    </section>

    <section class="card student-category-card">
        <div class="section-head">
            <div>
                <h2>Progress by Category</h2>
                <p class="muted">Monitor each important part of your student practicum journey.</p>
            </div>
        </div>
        <div class="student-category-list">
            <div class="student-category-item">
                <div class="student-category-top"><span>Profile Completion</span><strong><?= (int)$profilePercent ?>%</strong></div>
                <div class="student-category-bar"><span style="width: <?= (int)$profilePercent ?>%"></span></div>
                <small><?= !empty($student['profile_completed']) ? 'Your student profile is complete.' : 'Finish your profile details to fully unlock your account.' ?></small>
            </div>
            <div class="student-category-item">
                <div class="student-category-top"><span>Document Approval</span><strong><?= (int)$documentsPercent ?>%</strong></div>
                <div class="student-category-bar"><span style="width: <?= (int)$documentsPercent ?>%"></span></div>
                <small><?= e($approvedRequirements . ' approved out of ' . $totalRequirements . ' required documents') ?></small>
            </div>
            <div class="student-category-item">
                <div class="student-category-top"><span>OJT Hours</span><strong><?= (int)$hoursPercent ?>%</strong></div>
                <div class="student-category-bar"><span style="width: <?= (int)$hoursPercent ?>%"></span></div>
                <small><?= number_format($hours, 2) ?> logged hours • <?= number_format($remaining, 2) ?> hours remaining</small>
            </div>
        </div>
    </section>
</div>


