
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
        <div class="value warn"><?php echo $totalRegular; ?></div>
    </div>

    <div class="stat-card">
        <div class="label">Administrators</div>
        <div class="value alert"><?php echo $totalAdmins; ?></div>
    </div>

</div>

<div class="card">

    <div class="users-header">
        <h2>All users</h2>

        <a href="index.php?action=addUser" class="btn">
            + Add User
        </a>
    </div>

    <?php if (!empty($_SESSION['message'])): ?>
        <div class="message">
            <?php echo htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Account type</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($allUsers as $u): ?>

                    <tr>
                        <td>
                            <?php echo htmlspecialchars((string) $u->getId(), ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($u->getUsername(), ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($u->getMail(), ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <span class="badge <?php echo $u->getRole() === 'admin' ? 'badge-admin' : 'badge-client'; ?>">
                                <?php echo htmlspecialchars($u->getRole(), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($u->getUserType() ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <div class="action-buttons">

                                <a
                                    href="index.php?action=editUser&id=<?php echo (int) $u->getId(); ?>"
                                    class="btn"
                                >
                                    Edit
                                </a>

                                <?php if ((int) $u->getId() !== (int) ($_SESSION['user_id'] ?? 0)): ?>

                                    <form method="POST"
                                          action="index.php?action=deleteUser"
                                          onsubmit="return confirm('Are you sure you want to delete this user?');">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php echo (int) $u->getId(); ?>"
                                        >

                                        <button type="submit" class="btn btn-danger">
                                            Delete
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>

                <?php endforeach; ?>

                <?php if (empty($allUsers)): ?>
                    <tr>
                        <td colspan="6">No users found.</td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

<style>
.users-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 18px;
}

.users-header h2 {
    margin: 0;
}

.table-container {
    width: 100%;
    overflow-x: auto;
}

.action-buttons {
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-buttons form {
    margin: 0;
}

.message {
    padding: 12px;
    margin-bottom: 18px;
    border-radius: 7px;
    background: #e4efe7;
    color: var(--foret);
    font-size: 14px;
}

@media (max-width: 600px) {
    .users-header {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>

<?php

$content = ob_get_clean();
$title = 'User Management';
$activePage = 'users';

require __DIR__ . '/layout_admin.php';

