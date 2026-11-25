<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/AuthController.php';

class UserController
{
    private const ALL_ROLES = ['agriculteur', 'client', 'association', 'admin'];
    private const DEFAULT_PER_PAGE = 10;

    private PDO $db;
    private AuthController $auth;

    public function __construct(?AuthController $auth = null)
    {
        $this->db = Database::getConnexion();
        $this->auth = $auth ?? new AuthController();
    }

    public function getRoles(): array
    {
        return self::ALL_ROLES;
    }

    public function findById(int $id): ?UserModel
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare(
            'SELECT id, username, email, role, can_write, created_at FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ? UserModel::fromArray($row) : null;
    }

    public function countAllUsers(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users');
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countAdmins(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute([':role' => 'admin']);

        return (int) $stmt->fetchColumn();
    }

    public function getRoleCounts(): array
    {
        $counts = array_fill_keys(self::ALL_ROLES, 0);
        $stmt = $this->db->prepare('SELECT role, COUNT(*) AS total FROM users GROUP BY role');
        $stmt->execute();

        foreach ($stmt->fetchAll() as $row) {
            $role = (string) ($row['role'] ?? '');
            if (array_key_exists($role, $counts)) {
                $counts[$role] = (int) $row['total'];
            }
        }

        return $counts;
    }

    public function listUsers(
        string $search = '',
        string $roleFilter = '',
        int $page = 1,
        int $perPage = self::DEFAULT_PER_PAGE
    ): array {
        $this->auth->requireRole('admin');

        $search = trim($search);
        $roleFilter = trim($roleFilter);
        if (!in_array($roleFilter, self::ALL_ROLES, true)) {
            $roleFilter = '';
        }

        $perPage = max(1, min(50, $perPage));
        $page = max(1, $page);

        $conditions = [];
        $params = [];

        if ($search !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);
            $like = '%' . $escaped . '%';
            $conditions[] = '(username LIKE :search_username OR email LIKE :search_email)';
            $params[':search_username'] = $like;
            $params[':search_email'] = $like;
        }

        if ($roleFilter !== '') {
            $conditions[] = 'role = :role';
            $params[':role'] = $roleFilter;
        }

        $where = empty($conditions) ? '' : ' WHERE ' . implode(' AND ', $conditions);

        $countStmt = $this->db->prepare('SELECT COUNT(*) FROM users' . $where);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $listStmt = $this->db->prepare(
            'SELECT id, username, email, role, can_write, created_at FROM users'
            . $where
            . ' ORDER BY id ASC LIMIT :limit OFFSET :offset'
        );
        foreach ($params as $key => $value) {
            $listStmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $listStmt->execute();

        $users = array_map([UserModel::class, 'fromArray'], $listStmt->fetchAll());

        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'search' => $search,
            'role' => $roleFilter,
        ];
    }

    public function showUser(int $id): array
    {
        $this->auth->requireRole('admin');

        $user = $this->findById($id);
        if ($user === null) {
            return ['success' => false, 'errors' => ['user' => 'User not found.'], 'user' => null];
        }

        return ['success' => true, 'errors' => [], 'user' => $user];
    }

    public function createUser(array $post): array
    {
        $this->auth->requireRole('admin');

        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['csrf' => 'Invalid or expired security token.']];
        }

        $username = trim((string) ($post['username'] ?? ''));
        $email = strtolower(trim((string) ($post['email'] ?? '')));
        $password = (string) ($post['password'] ?? '');
        $passwordConfirm = (string) ($post['password_confirm'] ?? '');
        $role = trim((string) ($post['role'] ?? 'client'));
        $canWrite = (string) ($post['can_write'] ?? '1');

        $errors = $this->validateBaseFields($username, $email, $role, $canWrite);
        $errors += $this->validatePasswordFields($password, $passwordConfirm, true);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        if ($this->usernameExists($username)) {
            $errors['username'] = 'Username is already taken.';
        }

        if ($this->emailExists($email)) {
            $errors['email'] = 'Email is already registered.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (username, email, password, role, can_write)
                 VALUES (:username, :email, :password, :role, :can_write)'
            );
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hash,
                ':role' => $role,
                ':can_write' => (int) $canWrite,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return ['success' => false, 'errors' => ['general' => 'Username or email is already registered.']];
            }
            return ['success' => false, 'errors' => ['general' => 'Failed to create user. Please try again.']];
        }

        return ['success' => true, 'errors' => [], 'user_id' => (int) $this->db->lastInsertId()];
    }

    public function updateUser(int $id, array $post): array
    {
        $this->auth->requireRole('admin');

        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['csrf' => 'Invalid or expired security token.']];
        }

        $existingUser = $this->findById($id);
        if ($existingUser === null) {
            return ['success' => false, 'errors' => ['user' => 'User not found.']];
        }

        $username = trim((string) ($post['username'] ?? ''));
        $email = strtolower(trim((string) ($post['email'] ?? '')));
        $password = (string) ($post['password'] ?? '');
        $passwordConfirm = (string) ($post['password_confirm'] ?? '');
        $role = trim((string) ($post['role'] ?? ''));
        $canWrite = (string) ($post['can_write'] ?? '');

        $errors = $this->validateBaseFields($username, $email, $role, $canWrite);
        $errors += $this->validatePasswordFields($password, $passwordConfirm, false);

        if ($existingUser->getRole() === 'admin' && $role !== 'admin' && $this->countAdmins() <= 1) {
            $errors['role'] = 'The last administrator cannot be demoted.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        if ($this->usernameExists($username, $id)) {
            $errors['username'] = 'Username is already taken.';
        }

        if ($this->emailExists($email, $id)) {
            $errors['email'] = 'Email is already registered.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            if ($password !== '') {
                $stmt = $this->db->prepare(
                    'UPDATE users
                     SET username = :username, email = :email, password = :password, role = :role, can_write = :can_write
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => password_hash($password, PASSWORD_DEFAULT),
                    ':role' => $role,
                    ':can_write' => (int) $canWrite,
                    ':id' => $id,
                ]);
            } else {
                $stmt = $this->db->prepare(
                    'UPDATE users
                     SET username = :username, email = :email, role = :role, can_write = :can_write
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':role' => $role,
                    ':can_write' => (int) $canWrite,
                    ':id' => $id,
                ]);
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return ['success' => false, 'errors' => ['general' => 'Username or email is already registered.']];
            }
            return ['success' => false, 'errors' => ['general' => 'Failed to update user. Please try again.']];
        }

        if ($id === $this->currentUserId()) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['can_write'] = (int) $canWrite;
        }

        return ['success' => true, 'errors' => []];
    }

    public function toggleStatus(int $id, array $post): array
    {
        $this->auth->requireRole('admin');

        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['csrf' => 'Invalid or expired security token.']];
        }

        $user = $this->findById($id);
        if ($user === null) {
            return ['success' => false, 'errors' => ['user' => 'User not found.']];
        }

        $newStatus = $user->getCanWrite() ? 0 : 1;

        $stmt = $this->db->prepare('UPDATE users SET can_write = :can_write WHERE id = :id');
        $stmt->execute([
            ':can_write' => $newStatus,
            ':id' => $id,
        ]);

        if ($id === $this->currentUserId()) {
            $_SESSION['can_write'] = $newStatus;
        }

        return ['success' => true, 'errors' => [], 'can_write' => $newStatus];
    }

    public function canDeleteUser(UserModel $user): array
    {
        $id = (int) $user->getId();

        if ($id === $this->currentUserId()) {
            return ['allowed' => false, 'reason' => 'You cannot delete your own account.'];
        }

        if ($user->getRole() === 'admin' && $this->countAdmins() <= 1) {
            return ['allowed' => false, 'reason' => 'The last administrator cannot be deleted.'];
        }

        return ['allowed' => true, 'reason' => ''];
    }

    public function deleteUser(int $id, array $post): array
    {
        $this->auth->requireRole('admin');

        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['csrf' => 'Invalid or expired security token.']];
        }

        $user = $this->findById($id);
        if ($user === null) {
            return ['success' => false, 'errors' => ['user' => 'User not found.']];
        }

        $check = $this->canDeleteUser($user);
        if (!$check['allowed']) {
            return ['success' => false, 'errors' => ['general' => $check['reason']]];
        }

        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return ['success' => true, 'errors' => []];
    }

    private function validateBaseFields(string $username, string $email, string $role, string $canWrite): array
    {
        $errors = [];

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Username must be between 3 and 50 characters.';
        } elseif (!preg_match('/^[A-Za-z0-9_.-]+$/', $username)) {
            $errors['username'] = 'Username may only contain letters, numbers, dots, hyphens and underscores.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email address is invalid.';
        }

        if (!in_array($role, self::ALL_ROLES, true)) {
            $errors['role'] = 'Invalid role selected.';
        }

        if (!in_array($canWrite, ['0', '1'], true)) {
            $errors['can_write'] = 'Invalid status value.';
        }

        return $errors;
    }

    private function validatePasswordFields(string $password, string $passwordConfirm, bool $required): array
    {
        $errors = [];

        if (!$required && $password === '' && $passwordConfirm === '') {
            return $errors;
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors['password'] = 'Password must contain an uppercase letter, a lowercase letter and a digit.';
        }

        if ($passwordConfirm === '') {
            $errors['password_confirm'] = 'Password confirmation is required.';
        } elseif (!hash_equals($password, $passwordConfirm)) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        return $errors;
    }

    private function usernameExists(string $username, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1');
            $stmt->execute([':username' => $username, ':id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
            $stmt->execute([':username' => $username]);
        }

        return (bool) $stmt->fetch();
    }

    private function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
            $stmt->execute([':email' => $email, ':id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
        }

        return (bool) $stmt->fetch();
    }

    private function currentUserId(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
