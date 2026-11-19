<?php
$footerLoggedIn = $authController->isAuthenticated();
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <span class="footer-brand">
                    <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false">
                        <path fill="currentColor" d="M20 4C10 4 4 9 4 15c0 2 1 4 3 5 0-5 3-9 9-11-4 3-6 6-6.5 11C15 20 20 15 20 4z"/>
                    </svg>
                    AgriEco
                </span>
                <p class="footer-tagline" lang="fr">Du champ au client, sans intermédiaire</p>
                <div class="eco-scale" aria-label="Eco score scale">
                    <span class="eco-badge eco-a">Éco A</span>
                    <span class="eco-badge eco-b">Éco B</span>
                    <span class="eco-badge eco-c">Éco C</span>
                    <span class="eco-badge eco-d">Éco D</span>
                    <span class="eco-badge eco-e">Éco E</span>
                </div>
            </div>
            <div>
                <h3>Navigation</h3>
                <ul class="footer-links">
                    <li><a href="<?= BASE_URL ?>/index.php?action=home">Home</a></li>
                    <?php if ($footerLoggedIn): ?>
                        <li><a href="<?= BASE_URL ?>/index.php?action=dashboard">Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/index.php?action=login">Login</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php?action=register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div>
                <h3>AgriEco</h3>
                <p>Connecting farmers, customers and associations directly, for a fairer and greener food chain.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> AgriEco. All rights reserved.</span>
            <a class="back-to-top" href="#top">&uarr; Back to top</a>
        </div>
    </div>
</footer>