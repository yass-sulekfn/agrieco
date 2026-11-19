<?php
$bodyClass = 'front-office';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/navbar.php';
?>
<main class="site-main" id="main-content">
    <?php if (!empty($flash['message'])): ?>
        <div class="container">
            <div class="alert alert-<?= htmlspecialchars((string) ($flash['type'] ?? 'info'), ENT_QUOTES, 'UTF-8') ?>" role="alert">
                <?= htmlspecialchars((string) $flash['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    <?php endif; ?>
    <?= $content ?? '' ?>
</main>
<?php
require __DIR__ . '/partials/footer.php';
require __DIR__ . '/partials/scripts.php';