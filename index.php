<?php

ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

// HTTP Security Headers (sent before any output)
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; img-src 'self' data:;");

require_once __DIR__ . '/config/csrf.php';

$timeout = 1800;

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        $_SESSION = array();

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

        $_SESSION['message'] = "Your session has expired. Please login again.";
    }
}

$_SESSION['last_activity'] = time();

$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'home':
        echo "Welcome to Agrieco!";
        break;

    case 'register':
        require_once 'controllers/AuthController.php';
        break;

    case 'login':
        require_once 'controllers/AuthController.php';
        break;

    case 'logout':
        require_once 'controllers/AuthController.php';
        break;

    case 'users':
        require_once 'controllers/UserController.php';
        break;

    default:
        http_response_code(404);
        echo "Page not found.";
        break;
}
```[cite: 14]

Ready to move on to `SECURITY_TESTS.md` or commit `config/csrf.php` and `index.php` on `feature/security`?