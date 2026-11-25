<?php
final class AccessControl
{
    private static ?array $config = null;

    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/permissions.php';
        }
        return self::$config;
    }

    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function currentRole(): ?string
    {
        if (!self::isAuthenticated()) {
            return null;
        }
        $role = $_SESSION['role'] ?? null;
        return in_array($role, self::config()['roles'], true) ? $role : null;
    }

    public static function profile(): string
    {
        return self::currentRole() ?? 'visiteur';
    }

    public static function hasRole(string ...$roles): bool
    {
        $current = self::currentRole();
        return $current !== null && in_array($current, $roles, true);
    }

    public static function homeFor(?string $role): string
    {
        return self::config()['home'][$role ?? ''] ?? 'dashboard';
    }

    public static function can(string $action): bool
    {
        $cfg = self::config();

        foreach ($cfg['public'] as $pattern) {
            if (fnmatch($pattern, $action)) {
                return true;
            }
        }

        $role = self::currentRole();
        if ($role === null) {
            return false;
        }

        foreach ($cfg['rules'] as $pattern => $allowed) {
            if (fnmatch($pattern, $action)) {
                return in_array('*', $allowed, true) || in_array($role, $allowed, true);
            }
        }
        return false;
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: index.php?action=login');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();
        if (!self::hasRole(...$roles)) {
            self::deny();
        }
    }

    public static function guard(string $action): void
    {
        if (self::can($action)) {
            return;
        }
        self::requireAuth();
        self::deny();
    }

    public static function deny(): void
    {
        http_response_code(403);
        require __DIR__ . '/../views/403.php';
        exit;
    }

    public static function syncRoleFromDb(PDO $db): void
    {
        if (!self::isAuthenticated()) {
            return;
        }
        $stmt = $db->prepare('SELECT username, role FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => (int) $_SESSION['user_id']]);
        $row = $stmt->fetch();

        if (!$row) {
            $_SESSION = [];
            session_regenerate_id(true);
            return;
        }
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['role'];
    }

    public static function menu(): array
    {
        return self::config()['menu'][self::profile()] ?? [];
    }

    public static function adminMenu(): array
    {
        return self::config()['menu_admin'];
    }
}
