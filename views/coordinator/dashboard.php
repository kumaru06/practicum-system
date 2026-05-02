<div class="grid cards">
    <div class="card metric"><svg viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-8 8c.8-4 3.8-6 8-6s7.2 2 8 6H4Z"/></svg><div><strong><?= (int)$stats['students'] ?></strong><span>Total Students</span></div></div>
    <div class="card metric"><svg viewBox="0 0 24 24"><path d="m9 16.2-3.5-3.5L4 14.2 9 19l11-11-1.5-1.5L9 16.2Z"/></svg><div><strong><?= (int)$stats['enrolled'] ?></strong><span>Enrolled</span></div></div>
    <div class="card metric"><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 14-4-4 1.4-1.4 2.6 2.6 5.6-5.6L18 9l-7 7Z"/></svg><div><strong><?= (int)$stats['completed'] ?></strong><span>Completed OJT</span></div></div>
</div>

<div class="grid two">
    <section class="card">
        <div class="card-head"><h2>Create Student from COR</h2><p class="muted">Add a student account and securely store their uploaded registration document.</p></div>
        <form method="post" enctype="multipart/form-data" class="form js-validate">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="coordinator_create_student">
            <label>Student Number<input required name="student_no"></label>
            <label>Full Name<input required name="full_name"></label>
            <label>Email<input required type="email" name="email"></label>
            <label><select required name="course">
                <option value="">— Select course —</option>
                <option>Bachelor of Arts in Economics</option>
                <option>Bachelor of Arts in English Language</option>
                <option>Bachelor of Arts in Mass Communication</option>
                <option>Bachelor of Arts in Political Science</option>
                <option>Bachelor of Arts in Psychology</option>
                <option>Bachelor of Science in Accountancy</option>
                <option>Bachelor of Science in Business Administration Major in Financial Management</option>
                <option>Bachelor of Science in Business Administration Major in Human Resource Management</option>
                <option>Bachelor of Science in Business Administration Major in Management Information System</option>
                <option>Bachelor of Science in Business Administration Major in Marketing Management</option>
                <option>Bachelor of Science in Computer Engineering</option>
                <option>Bachelor of Science in Computer Science</option>
                <option>Bachelor of Science in Information Technology</option>
                <option>Bachelor of Science in Psychology</option>
                <option>Bachelor of Secondary Education Major in Computer Science</option>
                <option>Bachelor of Secondary Education Major in English</option>
                <option>Bachelor of Secondary Education Major in Mathematics</option>
                <option>Bachelor of Elementary Education</option>
            </select></label>
            <label>Year Level<input required name="year_level"></label>
            <label>COR PDF/JPG/PNG<input required type="file" name="cor_file" accept=".pdf,.jpg,.jpeg,.png"></label>
            <button class="btn btn-primary" type="submit"><span class="btn-text">Create Student</span><span class="spinner"></span></button>
        </form>
    </section>
    <section class="card">
        <div class="card-head"><h2>Enroll Student in OJT</h2><p class="muted">Follow the step-by-step wizard to assign a partner company and send deployment emails.</p></div>
        <form method="post" class="form js-validate wizard-form" data-wizard>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="coordinator_enroll_student">
            <div class="wizard-steps"><span class="active">Student</span><span>Company & Dates</span><span>Confirm</span></div>
            <div class="wizard-step active">
                <label><select required name="student_id"><option value="">— Select student —</option><?php foreach ($students as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['name'] . ' - ' . $s['student_no']) ?></option><?php endforeach; ?></select></label>
                <button class="btn btn-primary wizard-next" type="button">Next</button>
            </div>
            <div class="wizard-step">
                <label><select required name="company_id"><option value="">— Select company —</option><?php foreach ($companies as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></label>
                <label>Start Date<input required type="date" name="start_date"></label>
                <label>End Date<input required type="date" name="end_date"></label>
                <label>Required Hours<input required type="number" min="1" name="required_hours"></label>
                <div class="wizard-actions"><button class="btn btn-small wizard-prev" type="button">Back</button><button class="btn btn-primary wizard-next" type="button">Next</button></div>
            </div>
            <div class="wizard-step">
                <div class="confirm-box"></div>
                <div class="wizard-actions"><button class="btn btn-small wizard-prev" type="button">Back</button><button class="btn btn-primary" type="submit"><span class="btn-text">Enroll & Send Emails</span><span class="spinner"></span></button></div>
            </div>
        </form>
    </section>
</div>


