<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    // Roles a visitor may choose. "admin" is not in this list, so it cannot be self-assigned.
    private const PUBLIC_ROLES = ['client', 'agriculteur', 'association'];
    private const ALL_ROLES = ['agriculteur', 'client', 'association', 'admin'];
    private const SESSION_LIFETIME = 1800;
    private const LOGIN_URL = '/agrieco/index.php?action=login';
    private const DUMMY_HASH = '$2b$10$5K4DgXhuS5A3oAQAs7VGVukB/gZYaDijfN26n8F2sy.8ZK6W7Qete';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnexion();
        $this->startSession();
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public function getCsrfToken(): string
    {
        $this->startSession();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public function verifyCsrfToken(?string $token): bool
    {
        if ($token === null || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public function isAuthenticated(): bool
    {
        if (empty($_SESSION['user_id'])) {
            return false;
        }

        $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);

        if (time() - $lastActivity > self::SESSION_LIFETIME) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();

        return true;
    }

    public function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: ' . self::LOGIN_URL);
            exit;
        }
    }

    public function hasRole(string $role): bool
    {
        return $this->isAuthenticated() && ($_SESSION['role'] ?? null) === $role;
    }

    public function requireRole(string $role): void
    {
        $this->requireAuth();

        if (!$this->hasRole($role)) {
            http_response_code(403);
            exit('403 Forbidden');
        }
    }

    public function getRedirectAction(): string
    {
        // Admins go to the back-office panel; all other roles go to the user dashboard.
        return $this->hasRole('admin') ? 'admin' : 'dashboard';
    }

    public function canWrite(): bool
    {
        return $this->isAuthenticated() && (int) ($_SESSION['can_write'] ?? 0) === 1;
    }

    public function requireWriteAccess(): void
    {
        $this->requireAuth();

        if (!$this->canWrite()) {
            http_response_code(403);
            exit('403 Forbidden');
        }
    }

    public function register(array $post): array
    {
        $errors = [];

        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['csrf' => 'Invalid or expired security token.']];
        }

        $username = trim((string) ($post['username'] ?? ''));
        $email = strtolower(trim((string) ($post['email'] ?? '')));
        $password = (string) ($post['password'] ?? '');
        $passwordConfirm = (string) ($post['password_confirm'] ?? '');
        $role = trim((string) ($post['role'] ?? ''));

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

        // Server decides the allowed role and write flag; "admin" can never be chosen at signup.
        if (!in_array($role, self::PUBLIC_ROLES, true)) {
            $errors['role'] = 'Invalid role selected.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $errors['username'] = 'Username is already taken.';
        }

        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email is already registered.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Store the hash only. The plain password is never written to the database.
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO users (username, email, password, role, can_write)
                 VALUES (:username, :email, :password, :role, :can_write)'
            );
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hash,
                'role' => $role,
                'can_write' => 1,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return ['success' => false, 'errors' => ['general' => 'Username or email is already registered.']];
            }
            return ['success' => false, 'errors' => ['general' => 'Registration failed. Please try again.']];
        }

        return ['success' => true, 'errors' => [], 'user_id' => (int) $this->db->lastInsertId()];
    }

    public function login(array $post): array
    {
        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['csrf' => 'Invalid or expired security token.']];
        }

        $email = strtolower(trim((string) ($post['email'] ?? '')));
        $password = (string) ($post['password'] ?? '');
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare(
            'SELECT id, username, email, password, role, can_write FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Unknown email still runs password_verify on a dummy hash, so the response time stays similar.
        $hash = $user['password'] ?? self::DUMMY_HASH;
        $passwordValid = password_verify($password, $hash);

        // One generic message: do not say whether the email exists.
        if (!$user || !$passwordValid) {
            return ['success' => false, 'errors' => ['general' => 'Incorrect email or password.']];
        }

        // Replace the session id so a cookie captured before login cannot be reused.
        session_regenerate_id(true);

        unset($_SESSION['csrf_token']);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['can_write'] = (int) $user['can_write'];
        $_SESSION['last_activity'] = time();

        return ['success' => true, 'errors' => []];
    }

    public function logout(): void
    {
        // Drop session data, expire the cookie, then destroy the session on the server.
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?: 'Lax',
            ]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function findById(int $id): ?UserModel
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, role, can_write, created_at FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? UserModel::fromArray($row) : null;
    }

    public function getAllUsers(): array
    {
        $stmt = $this->db->query(
            'SELECT id, username, email, role, can_write, created_at FROM users ORDER BY id ASC'
        );

        return array_map([UserModel::class, 'fromArray'], $stmt->fetchAll());
    }

    public function countAllUsers(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function countByRole(string $role): int
    {
        if (!in_array($role, self::ALL_ROLES, true)) {
            return 0;
        }

        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = :role');
        $stmt->execute(['role' => $role]);

        return (int) $stmt->fetchColumn();
    }

    public function updateProfile(array $post): array
    {
        $this->requireAuth();

        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['csrf' => 'Invalid or expired security token.']];
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $errors = [];

        $username = trim((string) ($post['username'] ?? ''));
        $email = strtolower(trim((string) ($post['email'] ?? '')));
        $password = (string) ($post['password'] ?? '');

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

        if ($password !== '') {
            if (strlen($password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $errors['password'] = 'Password must contain an uppercase letter, a lowercase letter and a digit.';
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1');
        $stmt->execute(['username' => $username, 'id' => $userId]);
        if ($stmt->fetch()) {
            $errors['username'] = 'Username is already taken.';
        }

        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
        $stmt->execute(['email' => $email, 'id' => $userId]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email is already registered.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare('UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id');
            $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hash, 'id' => $userId]);
        } else {
            $stmt = $this->db->prepare('UPDATE users SET username = :username, email = :email WHERE id = :id');
            $stmt->execute(['username' => $username, 'email' => $email, 'id' => $userId]);
        }

        $_SESSION['username'] = $username;

        return ['success' => true, 'errors' => []];
    }

    public function updateUserAccess(array $post): array
    {
        $this->requireRole('admin');

        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['success' => false, 'errors' => ['csrf' => 'Invalid or expired security token.']];
        }

        $errors = [];

        $userId = filter_var($post['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $role = trim((string) ($post['role'] ?? ''));
        $canWrite = (string) ($post['can_write'] ?? '');

        if ($userId === false || $userId === null) {
            $errors['user_id'] = 'Invalid user.';
        }

        if (!in_array($role, self::ALL_ROLES, true)) {
            $errors['role'] = 'Invalid role.';
        }

        if (!in_array($canWrite, ['0', '1'], true)) {
            $errors['can_write'] = 'Invalid write access value.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        if ($this->findById($userId) === null) {
            return ['success' => false, 'errors' => ['user_id' => 'User not found.']];
        }

        $stmt = $this->db->prepare('UPDATE users SET role = :role, can_write = :can_write WHERE id = :id');
        $stmt->execute([
            'role' => $role,
            'can_write' => (int) $canWrite,
            'id' => $userId,
        ]);

        if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
            $_SESSION['role'] = $role;
            $_SESSION['can_write'] = (int) $canWrite;
        }

        return ['success' => true, 'errors' => []];
    }
}