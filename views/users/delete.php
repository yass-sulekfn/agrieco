<?php
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$userId = (int) $user->getId();
$userRole = $user->getRole();
$canDelete = (bool) ($canDelete ?? false);
$deleteBlockReason = (string) ($deleteBlockReason ?? '');
?>
<div class="flex flex-between mb-3">
    <div>
        <h1 class="mb-0">Confirm Deletion</h1>
        <p class="text-muted mb-0">Review the account before permanently deleting it.</p>
    </div>
    <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?action=users">Back to Users</a>
</div>

<article class="card" style="max-width:620px;border-top-color:var(--danger);">
    <?php if (!$canDelete): ?>
        <div class="alert alert-error" role="alert">
            <?= $esc($deleteBlockReason !== '' ? $deleteBlockReason : 'This user cannot be deleted.') ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Are you sure you want to permanently delete user <strong><?= $esc($user->getUsername()) ?></strong>? This action cannot be undone.
        </div>
    <?php endif; ?>

    <dl class="meta-list">
        <dt>ID</dt>
        <dd><?= $esc($userId) ?></dd>

        <dt>Username</dt>
        <dd><?= $esc($user->getUsername()) ?></dd>

        <dt>Email</dt>
        <dd><?= $esc($user->getEmail()) ?></dd>

        <dt>Role</dt>
        <dd><span class="badge badge-role-<?= $esc($userRole) ?>"><?= $esc($userRole) ?></span></dd>

        <dt>Status</dt>
        <dd>
            <?php if ($user->getCanWrite()): ?>
                <span class="badge badge-write">Read &amp; Write</span>
            <?php else: ?>
                <span class="badge badge-readonly">Read only</span>
            <?php endif; ?>
        </dd>
    </dl>

    <div class="flex mt-3">
        <?php if ($canDelete): ?>
            <form method="post" action="<?= BASE_URL ?>/index.php?action=users_destroy&amp;id=<?= $esc($userId) ?>">
                <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">
                <input type="hidden" name="id" value="<?= $esc($userId) ?>">
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </form>
        <?php endif; ?>
        <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?action=users">Cancel</a>
    </div>
</article>
