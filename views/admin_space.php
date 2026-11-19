<?php
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$users = $users ?? [];
$totalUsers = $totalUsers ?? count($users);
$roleCounts = $roleCounts ?? [];
$roles = $roles ?? ['agriculteur', 'client', 'association', 'admin'];
$errors = $errors ?? [];
$statusMessage = $statusMessage ?? '';
$currentAdminId = (int) ($_SESSION['user_id'] ?? 0);
?>
<div class="flex flex-between mb-3">
    <div>
        <h1 class="mb-0">User Management</h1>
        <p class="text-muted mb-0">Review accounts and adjust roles and write access.</p>
    </div>
</div>

<?php if ($statusMessage !== ''): ?>
    <div class="alert alert-success" role="status"><?= $esc($statusMessage) ?></div>
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

<section class="card mb-3" aria-label="Total accounts">
    <div class="stat-card">
        <span class="stat-label">Total accounts</span>
        <span class="stat-value"><?= $esc($totalUsers) ?></span>
    </div>
</section>

<section class="grid grid-4 mb-3" aria-label="Accounts by role">
    <?php foreach ($roles as $role): ?>
        <article class="card stat-card">
            <span class="stat-label"><span class="badge badge-role-<?= $esc($role) ?>"><?= $esc($role) ?></span></span>
            <span class="stat-value"><?= $esc($roleCounts[$role] ?? 0) ?></span>
        </article>
    <?php endforeach; ?>
</section>

<section aria-label="All users">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Username</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Access</th>
                    <th scope="col">Created</th>
                    <th scope="col">Update access</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No users found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $listedUser): ?>
                        <?php
                        $rowId = (int) $listedUser->getId();
                        $rowRole = $listedUser->getRole();
                        $rowCanWrite = $listedUser->getCanWrite();
                        $rowCreatedRaw = $listedUser->getCreatedAt();
                        $rowTimestamp = $rowCreatedRaw !== null ? strtotime($rowCreatedRaw) : false;
                        $rowCreated = $rowTimestamp !== false ? date('d M Y', $rowTimestamp) : (string) $rowCreatedRaw;
                        ?>
                        <tr>
                            <td><?= $esc($rowId) ?></td>
                            <td>
                                <?= $esc($listedUser->getUsername()) ?>
                                <?php if ($rowId === $currentAdminId): ?>
                                    <span class="text-muted">(you)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $esc($listedUser->getEmail()) ?></td>
                            <td><span class="badge badge-role-<?= $esc($rowRole) ?>"><?= $esc($rowRole) ?></span></td>
                            <td>
                                <?php if ($rowCanWrite): ?>
                                    <span class="badge badge-write">Read &amp; Write</span>
                                <?php else: ?>
                                    <span class="badge badge-readonly">Read only</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $esc($rowCreated) ?></td>
                            <td>
                                <form class="inline-form" method="post" action="<?= BASE_URL ?>/index.php?action=update_access">
                                    <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">
                                    <input type="hidden" name="user_id" value="<?= $esc($rowId) ?>">

                                    <label class="visually-hidden" for="role-<?= $esc($rowId) ?>">Role for <?= $esc($listedUser->getUsername()) ?></label>
                                    <select class="form-control form-control-sm" id="role-<?= $esc($rowId) ?>" name="role">
                                        <?php foreach ($roles as $roleOption): ?>
                                            <option value="<?= $esc($roleOption) ?>"<?= $rowRole === $roleOption ? ' selected' : '' ?>><?= $esc($roleOption) ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <label class="visually-hidden" for="can-write-<?= $esc($rowId) ?>">Write access for <?= $esc($listedUser->getUsername()) ?></label>
                                    <select class="form-control form-control-sm" id="can-write-<?= $esc($rowId) ?>" name="can_write">
                                        <option value="1"<?= $rowCanWrite ? ' selected' : '' ?>>Read / Write</option>
                                        <option value="0"<?= !$rowCanWrite ? ' selected' : '' ?>>Read Only</option>
                                    </select>

                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>