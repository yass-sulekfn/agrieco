
<?php

ob_start();

?>

<div class="container wide">

    <h1>
        Welcome, <?php echo htmlspecialchars($user->getUsername(), ENT_QUOTES, 'UTF-8'); ?> 👋
    </h1>

    <p>
        <span class="badge badge-user">
            Role: <?php echo htmlspecialchars($user->getUserType() ?? 'User', ENT_QUOTES, 'UTF-8'); ?>
        </span>
    </p>

    <table>
        <tr>
            <th>ID</th>
            <td><?php echo htmlspecialchars((string) $user->getId(), ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>

        <tr>
            <th>Username</th>
            <td><?php echo htmlspecialchars($user->getUsername(), ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>

        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($user->getMail(), ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>

        <tr>
            <th>Account type</th>
            <td><?php echo htmlspecialchars($user->getUserType() ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    </table>

    <p style="margin-top: 24px;">
        This is your personal space (frontoffice). Only administrators can access the backoffice.
    </p>

</div>

<?php

$content = ob_get_clean();
$title = 'My Space - Agrieco';

require __DIR__ . '/layout.php';

