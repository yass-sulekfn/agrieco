<?php
$bodyClass = 'back-office';
$currentPage = $currentPage ?? '';
$adminUsername = (string) ($_SESSION['username'] ?? '');
$adminRole = (string) ($_SESSION['role'] ?? '');
$adminCanWrite = (int) ($_SESSION['can_write'] ?? 0) === 1;
require __DIR__ . '/partials/head.php';
?>
<div class="admin-shell">
    <aside class="admin-sidebar" aria-label="Back office navigation">
        <div class="admin-brand">
            <span>AgriEco</span>
            <span class="backoffice-tag">BACKOFFICE</span>
        </div>
        <div class="admin-user">
            <span><?= htmlspecialchars($adminUsername, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="badge badge-role-<?= htmlspecialchars($adminRole, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($adminRole, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <nav class="admin-nav">
            <a class="admin-link<?= $currentPage === 'admin' ? ' active' : '' ?>"<?= $currentPage === 'admin' ? ' aria-current="page"' : '' ?> href="<?= BASE_URL ?>/index.php?action=admin">Dashboard</a>
            <a class="admin-link" href="<?= BASE_URL ?>/index.php?action=home">Front Office</a>
        </nav>
        <form class="admin-logout-form" method="post" action="<?= BASE_URL ?>/index.php?action=logout">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="admin-link">Logout</button>
        </form>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <p class="admin-crumb">Back Office / <strong><?= htmlspecialchars((string) ($adminSection ?? 'Dashboard'), ENT_QUOTES, 'UTF-8') ?></strong></p>
            <div class="admin-session">
                <span>Signed in as <?= htmlspecialchars($adminUsername, ENT_QUOTES, 'UTF-8') ?></span>
                <?php if ($adminCanWrite): ?>
                    <span class="badge badge-write">Read &amp; Write</span>
                <?php else: ?>
                    <span class="badge badge-readonly">Read only</span>
                <?php endif; ?>
            </div>
        </header>
        <main class="admin-content" id="main-content">
            <?php if (!empty($flash['message'])): ?>
                <div class="alert alert-<?= htmlspecialchars((string) ($flash['type'] ?? 'info'), ENT_QUOTES, 'UTF-8') ?>" role="alert">
                    <?= htmlspecialchars((string) $flash['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
            <?= $content ?? '' ?>
        </main>
    </div>
</div>
<?php
require __DIR__ . '/partials/scripts.php';