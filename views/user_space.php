<?php
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$profileRole = $user->getRole();
$profileCanWrite = isset($canWrite) ? (bool) $canWrite : $user->getCanWrite();
$createdRaw = $user->getCreatedAt();
$createdTimestamp = $createdRaw !== null ? strtotime($createdRaw) : false;
$createdDisplay = $createdTimestamp !== false ? date('d M Y, H:i', $createdTimestamp) : (string) $createdRaw;
$ecoLegend = [
    ['class' => 'eco-a', 'label' => 'Éco A', 'text' => 'Outstanding environmental performance.'],
    ['class' => 'eco-b', 'label' => 'Éco B', 'text' => 'Very good environmental performance.'],
    ['class' => 'eco-c', 'label' => 'Éco C', 'text' => 'Average environmental performance.'],
    ['class' => 'eco-d', 'label' => 'Éco D', 'text' => 'Below average environmental performance.'],
    ['class' => 'eco-e', 'label' => 'Éco E', 'text' => 'Poor environmental performance.'],
];
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
                <h2 class="card-title">Éco score legend</h2>
            </div>
            <p class="text-muted">Every AgriEco product carries an environmental rating from A to E.</p>
            <ul style="list-style:none;margin:0;padding:0;display:grid;gap:0.8rem;">
                <?php foreach ($ecoLegend as $item): ?>
                    <li class="flex">
                        <span class="eco-badge <?= $esc($item['class']) ?>"><?= $esc($item['label']) ?></span>
                        <span><?= $esc($item['text']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>
    </div>
</div>