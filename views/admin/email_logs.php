<section class="email-filter-bar">
    <?php $formatLogLabel = static fn(string $value): string => ucwords(str_replace('_', ' ', $value)); ?>
    <div class="section-head">
        <div>
            <h2>Filter Email Activity</h2>
            <p class="muted">Review real PHPMailer SMTP delivery attempts, failures, and errors.</p>
        </div>
        <span class="hero-pill soft">Real-time audit trail</span>
    </div>
    <form method="get" class="filter-bar email-filter-bare">
        <input type="hidden" name="r" value="admin_email_logs">
        <label class="filter-control">
            <span class="filter-label">Email Type</span>
            <span class="filter-select-wrap">
                <select name="type">
                    <option value="">All Types</option>
                    <?php foreach (['student_enrollment','company_deployment','password_reset'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>><?= e($formatLogLabel($type)) ?></option>
                    <?php endforeach; ?>
                </select>
            </span>
        </label>
        <label class="filter-control">
            <span class="filter-label">Delivery Status</span>
            <span class="filter-select-wrap">
                <select name="status">
                    <option value="">All Statuses</option>
                    <?php foreach (['sent','failed'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </span>
        </label>
        <label class="filter-control">
            <span class="filter-label">Date From</span>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
        </label>
        <label class="filter-control">
            <span class="filter-label">Date To</span>
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
        </label>
        <button class="btn btn-primary" type="submit">Apply Filters</button>
        <a class="btn btn-small" href="index.php?r=admin_email_logs">Reset</a>
    </form>
</section>

<section class="card">
    <div class="section-head section-head-split"><div><h2>Delivery History</h2><p class="muted">Search, export, and inspect every sent or failed message.</p></div><input class="table-search table-search-wide" placeholder="Search logs..."></div>
    <div class="table-wrap"><table class="data-table no-row-details"><thead><tr><th data-sort>Sent At</th><th data-sort>Recipient</th><th data-sort>Type</th><th>Action</th></tr></thead><tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= e($log['sent_at']) ?></td>
                <td><?= e($log['recipient_email']) ?></td>
                <td><span class="email-log-type"><?= e($formatLogLabel($log['type'])) ?></span></td>
                <td class="table-actions">
                    <button
                        class="btn btn-small btn-ghost email-log-view"
                        type="button"
                        data-sent-at="<?= e($log['sent_at']) ?>"
                        data-recipient="<?= e($log['recipient_email']) ?>"
                        data-subject="<?= e($log['subject']) ?>"
                        data-type="<?= e($log['type']) ?>"
                        data-status="<?= e($log['status']) ?>"
                        data-error="<?= e($log['error_message'] ?: 'No error message') ?>"
                    >
                        View
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody></table></div><div class="pagination"></div>
</section>
