<?php
$currentPage = $currentPage ?? '';
$isLoggedIn = $authController->isAuthenticated();
$isAdmin = $isLoggedIn && $authController->hasRole('admin');
$navUsername = (string) ($_SESSION['username'] ?? '');
$navRole = (string) ($_SESSION['role'] ?? '');
$navAttrs = static function (string $page, string $extraClass = '') use ($currentPage): string {
    $class = trim('nav-link ' . $extraClass . ($currentPage === $page ? ' active' : ''));
    return 'class="' . $class . '"' . ($currentPage === $page ? ' aria-current="page"' : '');
};
?>
<header class="site-header">
    <nav class="navbar container" aria-label="Main navigation">
        <a class="brand" href="<?= BASE_URL ?>/index.php?action=home">
            <svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false">
                <path fill="currentColor" d="M20 4C10 4 4 9 4 15c0 2 1 4 3 5 0-5 3-9 9-11-4 3-6 6-6.5 11C15 20 20 15 20 4z"/>
            </svg>
            <span>AgriEco</span>
        </a>
        <input type="checkbox" id="nav-toggle" class="nav-toggle" aria-label="Toggle navigation menu">
        <label for="nav-toggle" class="nav-burger" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </label>
        <ul class="nav-menu">
            <?php if ($isLoggedIn): ?>
                <li>
                    <span class="nav-user">
                        <?= htmlspecialchars($navUsername, ENT_QUOTES, 'UTF-8') ?>
                        <span class="badge badge-role-<?= htmlspecialchars($navRole, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($navRole, ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                </li>
                <li><a <?= $navAttrs('dashboard') ?> href="<?= BASE_URL ?>/index.php?action=dashboard">Dashboard</a></li>
                <?php if ($isAdmin): ?>
                    <li><a <?= $navAttrs('admin') ?> href="<?= BASE_URL ?>/index.php?action=admin">Admin Panel</a></li>
                <?php endif; ?>
                <li>
                    <form method="post" action="<?= BASE_URL ?>/index.php?action=logout">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="nav-link">Logout</button>
                    </form>
                </li>
            <?php else: ?>
                <li><a <?= $navAttrs('login') ?> href="<?= BASE_URL ?>/index.php?action=login">Login</a></li>
                <li><a <?= $navAttrs('register', 'nav-cta') ?> href="<?= BASE_URL ?>/index.php?action=register">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>