<section class="card password-gate">
    <div class="gate-icon"><svg viewBox="0 0 24 24"><path d="M17 8V7a5 5 0 0 0-10 0v1H5v14h14V8h-2ZM9 7a3 3 0 0 1 6 0v1H9V7Zm4 9.7V19h-2v-2.3a2 2 0 1 1 2 0Z"/></svg></div>
    <h2>Change your temporary password</h2>
    <p class="muted">For account security, temporary accounts must set a new password before accessing the portal.</p>
    <form action="{{ route('student.password.update') }}" method="post" class="form js-validate narrow-form">
        @csrf
        <label>New Password<input required minlength="8" type="password" name="password" autocomplete="new-password"></label>
        <label>Confirm New Password<input required minlength="8" type="password" name="confirm_password" autocomplete="new-password"></label>
        <button class="btn btn-primary" type="submit"><span class="btn-text">Save New Password</span><span class="spinner"></span></button>
    </form>
</section>
