<?php if (isset($extraScripts)): ?>
    <?php foreach ((array) $extraScripts as $extraScript): ?>
        <script src="<?= htmlspecialchars(BASE_URL . '/' . ltrim((string) $extraScript, '/'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>