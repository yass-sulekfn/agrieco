<?php
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$userId = (int) $user->getId();
$userRole = $user->getRole();
$userCanWrite = $user->getCanWrite();
$createdRaw = $user->getCreatedAt();
$createdTimestamp = $createdRaw !== null ? strtotime($createdRaw) : false;
$createdDisplay = $createdTimestamp !== false ? date('d M Y, H:i', $createdTimestamp) : (string) $createdRaw;
$canDelete = (bool) ($canDelete ?? false);
$deleteBlockReason = (string) ($deleteBlockReason ?? '');
$currentAdminId = (int) ($_SESSION['user_id'] ?? 0);
?>
<div class="flex flex-between mb-3">
    <div>
        <h1 class="mb-0">User Details</h1>
        <p class="text-muted mb-0">Account #<?= $esc($userId) ?> — <?= $esc($user->getUsername()) ?></p>
    </div>
    <a class="btn btn-outline" href="<?= BASE_URL ?>/index.php?action=users">Back to Users</a>
</div>

<article class="card" style="max-width:680px;">
    <div class="card-header">
        <h2 class="card-title">
            <?= $esc($user->getUsername()) ?>
            <?php if ($userId === $currentAdminId): ?>
                <span class="text-muted">(you)</span>
            <?php endif; ?>
        </h2>
        <span class="badge badge-role-<?= $esc($userRole) ?>"><?= $esc($userRole) ?></span>
    </div>

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
            <?php if ($userCanWrite): ?>
                <span class="badge badge-write">Read &amp; Write</span>
            <?php else: ?>
                <span class="badge badge-readonly">Read only</span>
            <?php endif; ?>
        </dd>

        <dt>Created at</dt>
        <dd><?= $esc($createdDisplay) ?></dd>
    </dl>

    <?php if (!$canDelete && $deleteBlockReason !== ''): ?>
        <div class="alert alert-info mt-3 mb-0" role="status">
            <?= $esc($deleteBlockReason) ?>
        </div>
    <?php endif; ?>

    <div class="flex mt-3">
        <a class="btn btn-secondary" href="<?= BASE_URL ?>/index.php?action=users_edit&amp;id=<?= $esc($userId) ?>">Edit User</a>

        <form class="inline-form" method="post" action="<?= BASE_URL ?>/index.php?action=users_toggle_status">
            <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">
            <input type="hidden" name="user_id" value="<?= $esc($userId) ?>">
            <input type="hidden" name="redirect_to" value="show">
            <?php if ($userCanWrite): ?>
                <button type="submit" class="btn btn-outline">Set Read Only</button>
            <?php else: ?>
                <button type="submit" class="btn btn-primary">Activate Read &amp; Write</button>
            <?php endif; ?>
        </form>

        <?php if ($canDelete): ?>
            <a class="btn btn-danger" href="<?= BASE_URL ?>/index.php?action=users_delete&amp;id=<?= $esc($userId) ?>">Delete User</a>
        <?php endif; ?>
    </div>
</article>
