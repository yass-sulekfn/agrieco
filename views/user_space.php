<?php
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$profileRole = $user->getRole();
$profileCanWrite = isset($canWrite) ? (bool) $canWrite : $user->getCanWrite();
$createdRaw = $user->getCreatedAt();
$createdTimestamp = $createdRaw !== null ? strtotime($createdRaw) : false;
$createdDisplay = $createdTimestamp !== false ? date('d M Y, H:i', $createdTimestamp) : (string) $createdRaw;
$errors = $errors ?? [];
$old = $old ?? [];
$invalid = static fn(string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
?>
<div class="container">
    <div class="page-header">
        <h1>Welcome, <?= $esc($user->getUsername()) ?></h1>
        <p class="text-muted">Your AgriEco personal space.</p>
    </div>

    <?php if (!$profileCanWrite): ?>
        <div class="alert alert-warning" role="alert">
            Your account is currently in read-only mode. Contact an administrator to enable write access.
        </div>
    <?php endif; ?>

    <div class="grid grid-2 section" style="padding-top:0;">
        <article class="card card-accent">
            <div class="card-header">
                <h2 class="card-title">My profile</h2>
                <span class="badge badge-role-<?= $esc($profileRole) ?>"><?= $esc($profileRole) ?></span>
            </div>
            <dl class="meta-list">
                <dt>ID</dt>
                <dd><?= $esc($user->getId()) ?></dd>

                <dt>Username</dt>
                <dd><?= $esc($user->getUsername()) ?></dd>

                <dt>Email</dt>
                <dd><?= $esc($user->getEmail()) ?></dd>

                <dt>Role</dt>
                <dd><span class="badge badge-role-<?= $esc($profileRole) ?>"><?= $esc($profileRole) ?></span></dd>

                <dt>Access</dt>
                <dd>
                    <?php if ($profileCanWrite): ?>
                        <span class="badge badge-write">Read &amp; Write</span>
                    <?php else: ?>
                        <span class="badge badge-readonly">Read only</span>
                    <?php endif; ?>
                </dd>

                <dt>Member since</dt>
                <dd><?= $esc($createdDisplay) ?></dd>
            </dl>
            <?php if ($profileRole === 'admin'): ?>
                <p class="mt-3 mb-0">
                    <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php?action=admin">Open the Admin Panel</a>
                </p>
            <?php endif; ?>
        </article>

        <article class="card card-accent">
            <div class="card-header">
                <h2 class="card-title">Edit my info</h2>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" role="alert">
                    <ul>
                        <?php foreach ($errors as $message): ?>
                            <li><?= $esc($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/index.php?action=update_profile">
                <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">

                <div class="form-group">
                    <label for="edit-username">Username</label>
                    <input
                        type="text"
                        id="edit-username"
                        name="username"
                        class="form-control<?= $invalid('username') ?>"
                        value="<?= $esc($old['username'] ?? $user->getUsername()) ?>"
                        minlength="3"
                        maxlength="50"
                        pattern="[A-Za-z0-9_.\-]+"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="edit-email">Email address</label>
                    <input
                        type="email"
                        id="edit-email"
                        name="email"
                        class="form-control<?= $invalid('email') ?>"
                        value="<?= $esc($old['email'] ?? $user->getEmail()) ?>"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="edit-password">New password <small class="text-muted">(leave blank to keep current)</small></label>
                    <input
                        type="password"
                        id="edit-password"
                        name="password"
                        class="form-control<?= $invalid('password') ?>"
                        minlength="8"
                        autocomplete="new-password"
                    >
                    <p class="form-hint">At least 8 characters, with an uppercase letter, a lowercase letter and a digit.</p>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Save changes</button>
            </form>
        </article>
    </div>
</div>
