
<?php

ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

header(
    "Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; img-src 'self' data:;"
);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/csrf.php';
require_once __DIR__ . '/controllers/AccessControl.php';
require_once __DIR__ . '/controllers/AuthController.php';

$timeout = 1800;

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        session_start();
        session_regenerate_id(true);

        $_SESSION['message'] = "Your session has expired. Please login again.";
    }
}

$_SESSION['last_activity'] = time();

requireCsrf();

$db = Database::getInstance()->getConnection();

AccessControl::syncRoleFromDb($db);

$controller = new AuthController();

$action = $_GET['action'] ?? 'home';

if (!is_string($action)) {
    $action = 'home';
}

AccessControl::guard($action);

switch ($action) {

    case 'home':

        if (AccessControl::isAuthenticated()) {
            $role = AccessControl::currentRole();

            if ($role === 'admin') {
                header('Location: index.php?action=admin');
            } else {
                header('Location: index.php?action=dashboard');
            }
        } else {
            header('Location: index.php?action=login');
        }

        exit;

    case 'register':

        $controller->register();
        break;

    case 'login':

        $controller->login();
        break;

    case 'dashboard':

        AccessControl::requireAuth();

        $user = $controller->findById((int) $_SESSION['user_id']);

        if (!$user) {
            $_SESSION = [];
            session_regenerate_id(true);

            header('Location: index.php?action=login');
            exit;
        }

        require __DIR__ . '/views/user_space.php';
        break;

    case 'admin':

        AccessControl::requireRole('admin');

        $user = $controller->findById((int) $_SESSION['user_id']);

        if (!$user) {
            $_SESSION = [];
            session_regenerate_id(true);

            header('Location: index.php?action=login');
            exit;
        }

        $allUsers = $controller->getAllUsers();
        $totalUsers = $controller->countAllUsers();
        $totalAdmins = $controller->countByRole('admin');
        $totalRegular = $controller->countByRole('user');

        require __DIR__ . '/views/admin_space.php';
        break;

    case 'logout':

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: index.php?action=login');
        exit;

    default:

        http_response_code(404);
        echo "Page not found.";
        break;
}

