<section class="card student-list-card">
    <div class="section-head section-head-split">
        <div><h2>My Students</h2><p class="muted">Switch between table and card view for a cleaner student overview.</p></div>
        <div class="toolbar-inline"><input class="table-search" placeholder="Search students..."><button class="btn btn-small view-toggle" type="button" data-target="studentsView">Card View</button></div>
    </div>
    <div id="studentsView" class="student-view-wrapper">
        <div class="student-cards-grid">
            <?php foreach ($students as $s): ?>
                <?php $required = (float)($s['required_hours'] ?? 0); $rendered = (float)($s['rendered_hours'] ?? 0); $percent = $required > 0 ? min(100, round(($rendered / $required) * 100)) : 0; ?>
                <article class="student-card" data-search="<?= e(strtolower($s['name'] . ' ' . $s['student_no'] . ' ' . $s['course'] . ' ' . ($s['company_name'] ?? ''))) ?>">
                    <div class="mini-ring" style="--percent: <?= $percent ?>"><span><?= $percent ?>%</span></div>
                    <div><h3><?= e($s['name']) ?></h3><p><?= e($s['student_no']) ?> · <?= e($s['course'] . ' ' . $s['year_level']) ?></p><span class="badge <?= e($s['deployment_status'] ?? 'pending') ?>"><?= e($s['deployment_status'] ?? 'pending') ?></span></div>
                    <small><?= e($s['company_name'] ?? 'No company assigned') ?></small>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="table-wrap"><table class="data-table"><thead><tr><th data-sort>Name</th><th data-sort>Student No.</th><th>Details</th></tr></thead><tbody>
            <?php foreach ($students as $s): ?>
                <?php $required = (float)($s['required_hours'] ?? 0); $rendered = (float)($s['rendered_hours'] ?? 0); $percent = $required > 0 ? min(100, round(($rendered / $required) * 100)) : 0; ?>
                <tr>
                    <td><?= e($s['name']) ?><br><small><?= e($s['email']) ?></small></td>
                    <td><?= e($s['student_no']) ?></td>
                    <td><button class="btn btn-small student-view-btn"
                            data-name="<?= e($s['name']) ?>"
                            data-email="<?= e($s['email']) ?>"
                            data-student-no="<?= e($s['student_no']) ?>"
                            data-course="<?= e($s['course'] . ' ' . $s['year_level']) ?>"
                            data-company="<?= e($s['company_name'] ?? '-') ?>"
                            data-status="<?= e($s['deployment_status'] ?? 'pending') ?>"
                            data-rendered="<?= number_format($rendered, 2) ?>"
                            data-required="<?= number_format($required, 2) ?>"
                            data-percent="<?= $percent ?>"
                            data-cor="<?= e($s['cor_file'] ?? '') ?>"
                            data-student-id="<?= (int)$s['id'] ?>"
                            data-csrf="<?= e(csrf_token()) ?>"
                            type="button">View</button></td>
                </tr>
            <?php endforeach; ?>
        </tbody></table></div><div class="pagination"></div>
    </div>
</section>

<div class="modal" id="studentModal">
    <div class="modal-card" style="max-width:540px;width:min(540px,94vw)">
        <button class="modal-close" id="studentModalClose">&times;</button>
        <h2 id="sm-name" style="margin:0 0 2px"></h2>
        <p id="sm-email" class="muted" style="margin:0 0 18px;font-size:.875rem"></p>
        <div class="sm-details-grid">
            <span class="sm-label">Student No.</span><span id="sm-student-no"></span>
            <span class="sm-label">Course</span><span id="sm-course"></span>
            <span class="sm-label">Company</span><span id="sm-company"></span>
            <span class="sm-label">Status</span><span id="sm-status"></span>
            <span class="sm-label">OJT Progress</span><span id="sm-progress"></span>
        </div>
        <div id="sm-cor-wrap" style="margin-top:18px;display:none">
            <a id="sm-cor-link" class="btn btn-small" target="_blank" href="#">View COR</a>
        </div>
        <details style="margin-top:20px">
            <summary style="cursor:pointer;font-size:.875rem;color:var(--accent)">Reset Password</summary>
            <form method="post" class="inline" style="margin-top:10px;display:flex;gap:8px;align-items:center">
                <input type="hidden" name="csrf_token" id="sm-csrf">
                <input type="hidden" name="action" value="coordinator_reset_password">
                <input type="hidden" name="student_id" id="sm-student-id">
                <button class="btn btn-small" type="submit">Send Reset Email</button>
            </form>
        </details>
    </div>
</div>
