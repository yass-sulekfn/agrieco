
<?php

$error = $error ?? '';
$success = $success ?? '';
$old = $old ?? [];

ob_start();

?>

<div class="register-card">

    <h1>Join AgriEco</h1>

    <p class="subtitle">
        Du champ au client, sans intermédiaire.
    </p>

    <?php if ($error != '') { ?>
        <div class="error">
            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <?php if ($success != '') { ?>
        <div class="success">
            <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <form method="POST" action="index.php?action=register">

        <?php echo csrfField(); ?>

        <div class="form-group">
            <label for="name">First Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="<?php echo htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="fam_name">Family Name</label>
            <input
                type="text"
                id="fam_name"
                name="fam_name"
                value="<?php echo htmlspecialchars($old['fam_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="phone_num">Phone Number</label>
            <input
                type="text"
                id="phone_num"
                name="phone_num"
                value="<?php echo htmlspecialchars($old['phone_num'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="address">Address (optional)</label>
            <input
                type="text"
                id="address"
                name="address"
                value="<?php echo htmlspecialchars($old['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="form-group">
            <label for="mail">Email Address</label>
            <input
                type="email"
                id="mail"
                name="mail"
                value="<?php echo htmlspecialchars($old['mail'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                required
            >
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                required
            >
        </div>

        <div class="form-group">
            <label for="user_type">I am registering as</label>

            <select id="user_type" name="user_type" required>
                <option value="">Choose your account type</option>

                <option value="client"
                    <?php if (($old['user_type'] ?? '') == 'client') echo 'selected'; ?>>
                    Client
                </option>

                <option value="farmer"
                    <?php if (($old['user_type'] ?? '') == 'farmer') echo 'selected'; ?>>
                    Farmer
                </option>

                <option value="association"
                    <?php if (($old['user_type'] ?? '') == 'association') echo 'selected'; ?>>
                    Association
                </option>
            </select>
        </div>

        <button type="submit">Create My Account</button>

    </form>

    <p class="login-link">
        Already have an account?
        <a href="index.php?action=login">Log in</a>
    </p>

</div>

<?php

$content = ob_get_clean();
$title = 'Register';

require __DIR__ . '/layout.php';

