<h2>OJT Orientation Notice</h2>
<p>Hello <?= e($student['student_name'] ?? $student['name'] ?? 'Student') ?>,</p>
<p>Your Industry Partner has scheduled your OJT orientation.</p>
<ul>
    <li><strong>Company:</strong> <?= e($company['name'] ?? '') ?></li>
    <li><strong>Orientation Date/Time:</strong> <?= e($orientationDateTime ?? '') ?></li>
    <li><strong>Notes:</strong> <?= e($notes ?? '') ?></li>
</ul>
<p>The OJT Coordinator has also been notified.</p>
