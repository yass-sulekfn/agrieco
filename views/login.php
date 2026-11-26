
<?php

require_once __DIR__ . '/../config/csrf.php';

$error = $error ?? '';
$mail = $mail ?? '';

ob_start();

?>

<div class="container">

    <h1>Welcome Back</h1>

    <p class="description">Login to your Agrieco account</p>

    <?php if (!empty($error)): ?>
        <p class="error"><?php echo e($error); ?></p>
    <?php endif; ?>

    <form action="index.php?action=login" method="POST">

        <?php echo csrfField(); ?>

        <label for="mail">Email</label>
        <input
            type="email"
            id="mail"
            name="mail"
            value="<?php echo e($mail); ?>"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <button type="submit">Login</button>

    </form>

    <div class="links">
        Don't have an account?
        <a href="index.php?action=register">Register</a>
    </div>

</div>

<?php

$content = ob_get_clean();
$title = 'Login';

require __DIR__ . '/layout.php';

