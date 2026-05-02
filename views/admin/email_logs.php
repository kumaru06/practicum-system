<section class="email-filter-bar">
    <div class="section-head">
        <div>
            <h2>Filter Email Activity</h2>
            <p class="muted">Review real PHPMailer SMTP delivery attempts, failures, and errors.</p>
        </div>
        <span class="hero-pill soft">Real-time audit trail</span>
    </div>
    <form method="get" class="filter-bar email-filter-bare">
        <input type="hidden" name="r" value="admin_email_logs">
        <select name="type">
            <option value="">All types</option>
            <?php foreach (['student_enrollment','company_deployment','password_reset'] as $type): ?>
            <option value="<?= e($type) ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach (['sent','failed'] as $status): ?>
            <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" placeholder="From" value="<?= e($filters['date_from'] ?? '') ?>">
        <input type="date" name="date_to" placeholder="To" value="<?= e($filters['date_to'] ?? '') ?>">
        <button class="btn btn-primary" type="submit">Apply Filters</button>
        <a class="btn btn-small" href="index.php?r=admin_email_logs">Reset</a>
    </form>
</section>

<section class="card">
    <div class="section-head section-head-split"><div><h2>Delivery History</h2><p class="muted">Search, export, and inspect every sent or failed message.</p></div><input class="table-search table-search-wide" placeholder="Search logs..."></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th data-sort>Sent At</th><th data-sort>Recipient</th><th data-sort>Subject</th><th data-sort>Type</th><th>Status</th><th>Error Message</th></tr></thead><tbody>
        <?php foreach ($logs as $log): ?><tr><td><?= e($log['sent_at']) ?></td><td><?= e($log['recipient_email']) ?></td><td><?= e($log['subject']) ?></td><td><?= e($log['type']) ?></td><td><span class="badge <?= e($log['status'] === 'sent' ? 'active' : 'inactive') ?>"><?= e($log['status']) ?></span></td><td><?= e($log['error_message'] ?? '') ?></td></tr><?php endforeach; ?>
    </tbody></table></div><div class="pagination"></div>
</section>
