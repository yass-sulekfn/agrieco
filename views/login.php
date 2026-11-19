<?php
$errors = $errors ?? [];
$old = $old ?? ['email' => ''];
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$hasEmailError = isset($errors['email']);
$hasPasswordError = isset($errors['password']);
?>
<section class="auth-wrapper">
    <div class="card auth-card">
        <h1>Log in to AgriEco</h1>
        <p class="text-center text-muted">Access your account to buy and sell directly, without intermediaries.</p>

        <?php if (!empty($_GET['registered'])): ?>
            <div class="alert alert-success" role="status">
                Your account has been created successfully. You can now log in.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul>
                    <?php foreach ($errors as $message): ?>
                        <li><?= $esc($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/index.php?action=login">
            <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">

            <div class="form-group">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control<?= $hasEmailError ? ' is-invalid' : '' ?>"
                    value="<?= $esc($old['email'] ?? '') ?>"
                    maxlength="150"
                    autocomplete="email"
                    <?= $hasEmailError ? 'aria-invalid="true"' : '' ?>
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control<?= $hasPasswordError ? ' is-invalid' : '' ?>"
                    autocomplete="current-password"
                    <?= $hasPasswordError ? 'aria-invalid="true"' : '' ?>
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log in</button>
        </form>

        <p class="auth-footer">
            New to AgriEco?
            <a href="<?= BASE_URL ?>/index.php?action=register">Create an account</a>
        </p>
    </div>
</section>