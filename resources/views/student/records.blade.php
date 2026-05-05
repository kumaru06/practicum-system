<div class="grid two">
    <?php if (!($canSubmitReports ?? false)): ?>
    <section class="card locked-card"><h2>Submit Daily Time Record</h2><p class="muted"><?= e($reportLockMessage ?? 'DTR is locked until your OJT deployment starts.') ?></p><button class="btn btn-primary" type="button" disabled>Submit DTR Locked</button></section>
    <section class="card locked-card"><h2>Submit Weekly Report</h2><p class="muted"><?= e($reportLockMessage ?? 'Weekly reports are locked until your OJT deployment starts.') ?></p><button class="btn btn-primary" type="button" disabled>Weekly Report Locked</button></section>
    <?php else: ?>
    <section class="card"><h2>Submit Daily Time Record</h2><form action="{{ route('student.dtr.store') }}" method="post" class="form js-validate">@csrf<label>Date<input required type="date" name="work_date"></label><label>Time In<input required type="time" name="time_in"></label><label>Time Out<input required type="time" name="time_out"></label><label>Tasks Done<textarea required maxlength="500" name="tasks_done"></textarea></label><button class="btn btn-primary" type="submit"><span class="btn-text">Submit DTR</span><span class="spinner"></span></button></form></section>
    <section class="card">
        <h2>Submit Weekly Report</h2>
        <form action="{{ route('student.weekly_reports.store') }}" method="post" enctype="multipart/form-data" class="form js-validate">
            @csrf
            <label>Week Number<input required type="number" min="1" name="week_no"></label>
            <label>PDF Report<input required type="file" name="report_file" accept=".pdf"></label>
            <button class="btn btn-primary" type="submit"><span class="btn-text">Submit Weekly Report</span><span class="spinner"></span></button>
        </form>
    </section>
    <?php endif; ?>
</div>
