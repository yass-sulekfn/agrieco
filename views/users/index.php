<?php
$esc = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$users = $users ?? [];
$totalUsers = $totalUsers ?? 0;
$filteredTotal = $filteredTotal ?? count($users);
$roleCounts = $roleCounts ?? [];
$roles = $roles ?? ['agriculteur', 'client', 'association', 'admin'];
$search = (string) ($search ?? '');
$roleFilter = (string) ($roleFilter ?? '');
$page = max(1, (int) ($page ?? 1));
$perPage = max(1, (int) ($perPage ?? 10));
$totalPages = max(1, (int) ($totalPages ?? 1));
$adminCount = (int) ($roleCounts['admin'] ?? 0);
$currentAdminId = (int) ($_SESSION['user_id'] ?? 0);

$buildQuery = static function (array $overrides = []) use ($search, $roleFilter, $page): string {
    $params = array_merge([
        'action' => 'users',
        'search' => $search,
        'role' => $roleFilter,
        'page' => $page,
    ], $overrides);

    if (($params['search'] ?? '') === '') {
        unset($params['search']);
    }
    if (($params['role'] ?? '') === '') {
        unset($params['role']);
    }
    if ((int) ($params['page'] ?? 1) <= 1) {
        unset($params['page']);
    }

    return BASE_URL . '/index.php?' . http_build_query($params);
};

$startItem = $filteredTotal > 0 ? (($page - 1) * $perPage) + 1 : 0;
$endItem = $filteredTotal > 0 ? min($filteredTotal, $page * $perPage) : 0;
?>
<div class="flex flex-between mb-3">
    <div>
        <h1 class="mb-0">Users</h1>
        <p class="text-muted mb-0">Manage platform accounts, roles and access status.</p>
    </div>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/index.php?action=users_create">+ Add User</a>
</div>

<section class="card mb-3" aria-label="Total accounts">
    <div class="stat-card">
        <span class="stat-label">Total accounts</span>
        <span class="stat-value"><?= $esc($totalUsers) ?></span>
    </div>
</section>

<section class="grid grid-4 mb-3" aria-label="Accounts by role">
    <?php foreach ($roles as $roleOption): ?>
        <article class="card stat-card">
            <span class="stat-label"><span class="badge badge-role-<?= $esc($roleOption) ?>"><?= $esc($roleOption) ?></span></span>
            <span class="stat-value"><?= $esc($roleCounts[$roleOption] ?? 0) ?></span>
        </article>
    <?php endforeach; ?>
</section>

<section class="card mb-3" aria-label="Search and filter users">
    <form class="inline-form" method="get" action="<?= BASE_URL ?>/index.php" style="justify-content:space-between;">
        <input type="hidden" name="action" value="users">
        <div class="inline-form" style="flex:1;min-width:260px;">
            <label class="visually-hidden" for="users-search">Search by username or email</label>
            <input
                type="search"
                id="users-search"
                name="search"
                class="form-control form-control-sm"
                placeholder="Search username or email..."
                value="<?= $esc($search) ?>"
                style="max-width:300px;"
            >

            <label class="visually-hidden" for="users-role-filter">Filter by role</label>
            <select id="users-role-filter" name="role" class="form-control form-control-sm" style="max-width:190px;">
                <option value="">All roles</option>
                <?php foreach ($roles as $roleOption): ?>
                    <option value="<?= $esc($roleOption) ?>"<?= $roleFilter === $roleOption ? ' selected' : '' ?>><?= $esc($roleOption) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php if ($search !== '' || $roleFilter !== ''): ?>
                <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/index.php?action=users">Reset</a>
            <?php endif; ?>
        </div>
        <span class="text-muted" style="font-size:0.9rem;">
            Showing <?= $esc($startItem) ?>–<?= $esc($endItem) ?> of <?= $esc($filteredTotal) ?>
        </span>
    </form>
</section>

<section aria-label="Users list">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Username</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Status</th>
                    <th scope="col">Created</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No users match your criteria.</td>
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
                        $isSelf = $rowId === $currentAdminId;
                        $isLastAdmin = $rowRole === 'admin' && $adminCount <= 1;
                        ?>
                        <tr>
                            <td><?= $esc($rowId) ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/index.php?action=users_show&amp;id=<?= $esc($rowId) ?>">
                                    <strong><?= $esc($listedUser->getUsername()) ?></strong>
                                </a>
                                <?php if ($isSelf): ?>
                                    <span class="text-muted">(you)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $esc($listedUser->getEmail()) ?></td>
                            <td><span class="badge badge-role-<?= $esc($rowRole) ?>"><?= $esc($rowRole) ?></span></td>
                            <td>
                                <form class="inline-form" method="post" action="<?= BASE_URL ?>/index.php?action=users_toggle_status">
                                    <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken ?? '') ?>">
                                    <input type="hidden" name="user_id" value="<?= $esc($rowId) ?>">
                                    <input type="hidden" name="search" value="<?= $esc($search) ?>">
                                    <input type="hidden" name="role" value="<?= $esc($roleFilter) ?>">
                                    <input type="hidden" name="page" value="<?= $esc($page) ?>">
                                    <?php if ($rowCanWrite): ?>
                                        <span class="badge badge-write">Read &amp; Write</span>
                                        <button type="submit" class="btn btn-outline btn-sm" title="Switch to Read only">Restrict</button>
                                    <?php else: ?>
                                        <span class="badge badge-readonly">Read only</span>
                                        <button type="submit" class="btn btn-primary btn-sm" title="Enable Read &amp; Write">Activate</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td><?= $esc($rowCreated) ?></td>
                            <td>
                                <div class="inline-form">
                                    <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/index.php?action=users_show&amp;id=<?= $esc($rowId) ?>">View</a>
                                    <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/index.php?action=users_edit&amp;id=<?= $esc($rowId) ?>">Edit</a>
                                    <?php if (!$isSelf && !$isLastAdmin): ?>
                                        <a class="btn btn-danger btn-sm" href="<?= BASE_URL ?>/index.php?action=users_delete&amp;id=<?= $esc($rowId) ?>">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($totalPages > 1): ?>
    <nav class="flex flex-between mt-3" aria-label="Users pagination">
        <span class="text-muted">Page <?= $esc($page) ?> of <?= $esc($totalPages) ?></span>
        <div class="inline-form">
            <?php if ($page > 1): ?>
                <a class="btn btn-outline btn-sm" href="<?= $esc($buildQuery(['page' => $page - 1])) ?>">Previous</a>
            <?php endif; ?>

            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p === $page): ?>
                    <span class="btn btn-primary btn-sm" aria-current="page"><?= $esc($p) ?></span>
                <?php else: ?>
                    <a class="btn btn-outline btn-sm" href="<?= $esc($buildQuery(['page' => $p])) ?>"><?= $esc($p) ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a class="btn btn-outline btn-sm" href="<?= $esc($buildQuery(['page' => $page + 1])) ?>">Next</a>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>
