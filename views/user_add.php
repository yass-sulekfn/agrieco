
<?php

ob_start();

$old = $old ?? [];
$error = $error ?? '';
$csrfToken = $csrfToken ?? getCsrfToken();

?>

<div class="card">
    <h2>Add a new user</h2>

    <?php if ($error !== ''): ?>
        <div class="form-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=addUser">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-grid">

            <div class="form-group">
                <label for="name">First name *</label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="fam_name">Last name *</label>
                <input type="text" id="fam_name" name="fam_name" required
                       value="<?= htmlspecialchars($old['fam_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="phone_num">Phone number *</label>
                <input type="tel" id="phone_num" name="phone_num" required
                       value="<?= htmlspecialchars($old['phone_num'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="mail">Email *</label>
                <input type="email" id="mail" name="mail" required
                       value="<?= htmlspecialchars($old['mail'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group full-width">
                <label for="address">Address</label>
                <input type="text" id="address" name="address"
                       value="<?= htmlspecialchars($old['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>

            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="user" <?= ($old['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>Normal user</option>
                    <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
            </div>

            <div class="form-group">
                <label for="user_type">Account type</label>
                <select id="user_type" name="user_type">
                    <option value="">None</option>
                    <option value="farmer" <?= ($old['user_type'] ?? '') === 'farmer' ? 'selected' : '' ?>>Farmer</option>
                    <option value="client" <?= ($old['user_type'] ?? '') === 'client' ? 'selected' : '' ?>>Client</option>
                    <option value="association" <?= ($old['user_type'] ?? '') === 'association' ? 'selected' : '' ?>>Association</option>
                </select>
            </div>

        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Create user</button>
            <a href="index.php?action=admin" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group label {
    font-size: 14px;
    font-weight: 700;
    color: var(--texte);
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid var(--ligne);
    border-radius: 7px;
    background: #fff;
    color: var(--texte);
    font: 14px var(--sans);
}

.form-group input:focus,
.form-group select:focus {
    outline: 2px solid var(--sauge);
    border-color: var(--feuille);
}

.full-width {
    grid-column: 1 / -1;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
}

.btn-secondary {
    background: var(--texte-doux);
}

.btn-secondary:hover {
    background: var(--foret);
}

.form-error {
    padding: 12px;
    margin-bottom: 18px;
    border-radius: 7px;
    background: #f4dcd7;
    color: var(--brique);
    font-size: 14px;
}

@media (max-width: 600px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .full-width {
        grid-column: auto;
    }
}
</style>

<?php

$content = ob_get_clean();
$title = 'Add User';
$activePage = 'users';

require __DIR__ . '/layout_admin.php';

