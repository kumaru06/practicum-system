<div class="grid two">
    <section class="card">
        <div class="card-head"><h2>Create Partner Company</h2><p class="muted">Add a new industry partner and prepare dashboard access.</p></div>
        <form method="post" class="form js-validate">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="admin_create_company">
            <label>Company Name<input required name="company_name"></label>
            <label>Contact Person<input required name="contact_person"></label>
            <label>Contact Email<input required type="email" name="contact_email"></label>
            <label>Address<textarea required name="address"></textarea></label>
            <label>Password (optional)<input minlength="8" type="password" name="password"></label>
            <button class="btn btn-primary" type="submit"><span class="btn-text">Create Company</span><span class="spinner"></span></button>
        </form>
    </section>

    <section class="card">
        <div class="card-head"><h2>All Partner Companies</h2><p class="muted">Registered industry partners in the system.</p></div>
        <div class="table-wrap"><table class="data-table"><thead><tr><th data-sort>Company</th><th data-sort>Contact</th><th data-sort>Email</th><th>Status</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($partners as $u): ?>
            <tr>
                <td><?= e($u['name']) ?></td>
                <td><?= e($u['contact_person'] ?? '—') ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge <?= $u['is_active'] ? 'active' : 'inactive' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td>
                    <form method="post" class="inline">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="action" value="admin_toggle_user">
                        <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                        <input type="hidden" name="active" value="<?= $u['is_active'] ? 0 : 1 ?>">
                        <input type="hidden" name="redirect" value="admin_partners">
                        <button class="btn btn-small" type="submit"><?= $u['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody></table></div>
    </section>
</div>
