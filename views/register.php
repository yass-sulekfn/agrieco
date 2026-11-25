<?php
$errors = $errors ?? [];
$old = $old ?? ['username' => '', 'email' => '', 'role' => 'client'];
// Escape every dynamic output with htmlspecialchars() to prevent XSS.
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$roleOptions = [
    'client' => 'Client',
    'agriculteur' => 'Farmer',
    'association' => 'Association',
];
$selectedRole = array_key_exists((string) ($old['role'] ?? ''), $roleOptions) ? $old['role'] : 'client';
$invalid = static fn(string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
$ariaInvalid = static fn(string $field): string => isset($errors[$field]) ? 'aria-invalid="true"' : '';
?>
<section class="auth-wrapper">
    <div class="card auth-card">
        <h1>Join AgriEco</h1>
        <p class="text-center text-muted">Du champ au client, sans intermédiaire.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert">
                <ul>
                    <?php foreach ($errors as $message): ?>
                        <li><?= $esc($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/index.php?action=register">
            <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control<?= $invalid('username') ?>"
                    value="<?= $esc($old['username'] ?? '') ?>"
                    minlength="3"
                    maxlength="50"
                    pattern="[A-Za-z0-9_.\-]+"
                    autocomplete="username"
                    <?= $ariaInvalid('username') ?>
                    required
                    autofocus
                >
                <p class="form-hint">3 to 50 characters: letters, numbers, dots, hyphens and underscores.</p>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control<?= $invalid('email') ?>"
                    value="<?= $esc($old['email'] ?? '') ?>"
                    maxlength="150"
                    autocomplete="email"
                    <?= $ariaInvalid('email') ?>
                    required
                >
            </div>

            <fieldset class="form-group" style="border:0;padding:0;margin:0 0 1.2rem;">
                <legend class="form-label">I am registering as</legend>
                <div class="choice-group">
                    <?php foreach ($roleOptions as $value => $label): ?>
                        <div class="choice">
                            <input
                                type="radio"
                                id="role-<?= $esc($value) ?>"
                                name="role"
                                value="<?= $esc($value) ?>"
                                <?= $selectedRole === $value ? 'checked' : '' ?>
                                required
                            >
                            <label for="role-<?= $esc($value) ?>"><?= $esc($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control<?= $invalid('password') ?>"
                        minlength="8"
                        autocomplete="new-password"
                        <?= $ariaInvalid('password') ?>
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirm password</label>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        class="form-control<?= $invalid('password_confirm') ?>"
                        minlength="8"
                        autocomplete="new-password"
                        <?= $ariaInvalid('password_confirm') ?>
                        required
                    >
                </div>
            </div>
            <p class="form-hint mb-2">At least 8 characters, with an uppercase letter, a lowercase letter and a digit.</p>

            <button type="submit" class="btn btn-primary btn-block">Create my account</button>
        </form>

        <p class="auth-footer">
            Already have an account?
            <a href="<?= BASE_URL ?>/index.php?action=login">Log in</a>
        </p>
    </div>
</section>