
<?php

ob_start();

?>

<div class="stats-grid">

    <div class="stat-card">
        <div class="label">Total accounts</div>
        <div class="value"><?php echo $totalUsers; ?></div>
    </div>

    <div class="stat-card">
        <div class="label">Regular users</div>
        <div class="value accent"><?php echo $totalRegular; ?></div>
    </div>

    <div class="stat-card">
        <div class="label">Administrators</div>
        <div class="value red"><?php echo $totalAdmins; ?></div>
    </div>

</div>

<div class="card">

    <h2>All users</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Account type</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($allUsers as $u): ?>

                <tr>
                    <td><?php echo htmlspecialchars((string) $u->getId(), ENT_QUOTES, 'UTF-8'); ?></td>

                    <td><?php echo htmlspecialchars($u->getUsername(), ENT_QUOTES, 'UTF-8'); ?></td>

                    <td><?php echo htmlspecialchars($u->getMail(), ENT_QUOTES, 'UTF-8'); ?></td>

                    <td>
                        <span class="badge <?php echo $u->getRole() === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                            <?php echo htmlspecialchars($u->getRole(), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>

                    <td><?php echo htmlspecialchars($u->getUserType() ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>

            <?php endforeach; ?>

        </tbody>
    </table>

</div>

<?php

$content = ob_get_clean();
$title = 'Admin Dashboard';
$activePage = 'dashboard';

require __DIR__ . '/layout_admin.php';

