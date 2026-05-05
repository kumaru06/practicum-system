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
