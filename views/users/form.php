<?php
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$isEdit = (bool) ($isEdit ?? false);
$userId = isset($userId) ? (int) $userId : 0;
$roles = $roles ?? ['agriculteur', 'client', 'association', 'admin'];
$errors = $errors ?? [];
$old = $old ?? ['username' => '', 'email' => '', 'role' => 'client', 'can_write' => '1'];
$isLastAdmin = (bool) ($isLastAdmin ?? false);
$formAction = $isEdit
    ? BASE_URL . '/index.php?action=users_update&id=' . $userId
    : BASE_URL . '/index.php?action=users_store';
$invalid = static fn(string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
$ariaInvalid = static fn(string $field): string => isset($errors[$field]) ? 'aria-invalid="true"' : '';
?>
<div class="flex flex-between mb-3">
    <div>
        <h1 class="mb-0"><?= $isEdit ? 'Edit User' : 'Add User' ?></h1>
        <p class="text-muted mb-0">
            <?= $isEdit ? 'Update account details, role and access status.' : 'Create a new AgriEco user account.' ?>
        </p>
    </div>
    <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?action=users">Back to Users</a>
</div>

<article class="card" style="max-width:680px;">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= $esc($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($isEdit && $isLastAdmin): ?>
        <div class="alert alert-warning" role="status">
            This account is the last administrator and cannot be demoted to another role.
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $esc($formAction) ?>">
        <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $esc($userId) ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                class="form-control<?= $invalid('username') ?>"
                value="<?= $esc($old['username'] ?? '') ?>"
                minlength="3"
                maxlength="50"
                pattern="[A-Za-z0-9_.\-]+"
                <?= $ariaInvalid('username') ?>
                required
                autofocus
            >
            <p class="form-hint">3 to 50 characters: letters, numbers, dots, hyphens and underscores.</p>
            <?php if (isset($errors['username'])): ?>
                <p class="field-error"><?= $esc($errors['username']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control<?= $invalid('email') ?>"
                value="<?= $esc($old['email'] ?? '') ?>"
                maxlength="150"
                <?= $ariaInvalid('email') ?>
                required
            >
            <?php if (isset($errors['email'])): ?>
                <p class="field-error"><?= $esc($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="role">Role</label>
                <select
                    id="role"
                    name="role"
                    class="form-control<?= $invalid('role') ?>"
                    <?= $ariaInvalid('role') ?>
                    required
                >
                    <?php foreach ($roles as $roleOption): ?>
                        <option
                            value="<?= $esc($roleOption) ?>"
                            <?= ($old['role'] ?? '') === $roleOption ? ' selected' : '' ?>
                            <?= ($isEdit && $isLastAdmin && $roleOption !== 'admin') ? ' disabled' : '' ?>
                        >
                            <?= $esc($roleOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <p class="field-error"><?= $esc($errors['role']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="can_write">Status</label>
                <select
                    id="can_write"
                    name="can_write"
                    class="form-control<?= $invalid('can_write') ?>"
                    <?= $ariaInvalid('can_write') ?>
                    required
                >
                    <option value="1"<?= (string) ($old['can_write'] ?? '1') === '1' ? ' selected' : '' ?>>Read &amp; Write</option>
                    <option value="0"<?= (string) ($old['can_write'] ?? '1') === '0' ? ' selected' : '' ?>>Read Only</option>
                </select>
                <?php if (isset($errors['can_write'])): ?>
                    <p class="field-error"><?= $esc($errors['can_write']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password"><?= $isEdit ? 'New password (optional)' : 'Password' ?></label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control<?= $invalid('password') ?>"
                    minlength="8"
                    autocomplete="new-password"
                    <?= $ariaInvalid('password') ?>
                    <?= $isEdit ? '' : 'required' ?>
                >
                <?php if (isset($errors['password'])): ?>
                    <p class="field-error"><?= $esc($errors['password']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm"><?= $isEdit ? 'Confirm new password' : 'Confirm password' ?></label>
                <input
                    type="password"
                    id="password_confirm"
                    name="password_confirm"
                    class="form-control<?= $invalid('password_confirm') ?>"
                    minlength="8"
                    autocomplete="new-password"
                    <?= $ariaInvalid('password_confirm') ?>
                    <?= $isEdit ? '' : 'required' ?>
                >
                <?php if (isset($errors['password_confirm'])): ?>
                    <p class="field-error"><?= $esc($errors['password_confirm']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <p class="form-hint mb-3">
            <?= $isEdit
                ? 'Leave both password fields blank to keep the current password. Otherwise at least 8 characters with uppercase, lowercase and digit.'
                : 'At least 8 characters, with an uppercase letter, a lowercase letter and a digit.' ?>
        </p>

        <div class="flex">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create User' ?></button>
            <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?action=users">Cancel</a>
        </div>
    </form>
</article>
