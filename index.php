<?php

declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '1' : '0');

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const BASE_URL = '/agrieco';
const SESSION_TIMEOUT = 1800;

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';

function redirect(string $action, array $params = []): void
{
    $query = http_build_query(['action' => $action] + $params);
    header('Location: ' . BASE_URL . '/index.php?' . $query);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flattenErrors(array $errors): string
{
    return implode(' ', array_map('strval', array_values($errors)));
}

function inputString(array $source, string $key): string
{
    return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : '';
}

function requireAdminAccess(AuthController $authController): void
{
    if (!$authController->isAuthenticated()) {
        setFlash('warning', 'Please log in to continue.');
        redirect('login');
    }

    $authController->requireRole('admin');
}

function render(AuthController $authController, string $view, array $data = [], string $layout = 'layout'): void
{
    $data['authController'] = $authController;
    $data['csrfToken'] = $authController->getCsrfToken();

    if (!array_key_exists('flash', $data)) {
        $data['flash'] = $_SESSION['flash'] ?? null;
    }
    unset($_SESSION['flash']);

    extract($data, EXTR_SKIP);

    $viewFile = __DIR__ . '/views/' . $view . '.php';

    ob_start();
    if (is_file($viewFile)) {
        require $viewFile;
    } else {
        echo '<section class="container section"><div class="card"><h1>'
            . htmlspecialchars((string) ($pageTitle ?? 'AgriEco'), ENT_QUOTES, 'UTF-8')
            . '</h1><p>The requested content is not available.</p></div></section>';
    }
    $content = ob_get_clean();

    require __DIR__ . '/views/' . $layout . '.php';
}

try {
    $authController = new AuthController();
    $userController = new UserController($authController);
} catch (RuntimeException $e) {
    http_response_code(500);
    exit('Service temporarily unavailable.');
}

if (isset($_SESSION['user_id'])) {
    $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);

    if (time() - $lastActivity > SESSION_TIMEOUT) {
        $authController->logout();
        redirect('login', ['expired' => 1]);
    }

    $_SESSION['last_activity'] = time();
}

$action = $_GET['action'] ?? 'home';
$action = is_string($action) ? $action : 'home';
$isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$post = array_filter($_POST, 'is_string');

switch ($action) {
    case 'home':
        render($authController, 'home', [
            'pageTitle' => 'AgriEco | Du champ au client, sans intermédiaire',
            'currentPage' => 'home',
        ]);
        break;

    case 'register':
        if ($authController->isAuthenticated()) {
            redirect('dashboard');
        }

        $errors = [];
        $old = ['username' => '', 'email' => '', 'role' => 'client'];

        if ($isPost) {
            $old = [
                'username' => inputString($post, 'username'),
                'email' => inputString($post, 'email'),
                'role' => inputString($post, 'role') ?: 'client',
            ];

            $result = $authController->register($post);

            if ($result['success']) {
                redirect('login', ['registered' => 1]);
            }

            $errors = $result['errors'];
        }

        render($authController, 'register', [
            'pageTitle' => 'Register | AgriEco',
            'currentPage' => 'register',
            'errors' => $errors,
            'old' => $old,
        ]);
        break;

    case 'login':
        if ($authController->isAuthenticated()) {
            redirect($authController->hasRole('admin') ? 'admin' : 'dashboard');
        }

        $errors = [];
        $old = ['email' => ''];
        $data = [
            'pageTitle' => 'Login | AgriEco',
            'currentPage' => 'login',
        ];

        if ($isPost) {
            $old['email'] = inputString($post, 'email');

            $result = $authController->login($post);

            if ($result['success']) {
                setFlash('success', 'Welcome back, ' . (string) ($_SESSION['username'] ?? '') . '.');
                redirect($authController->hasRole('admin') ? 'admin' : 'dashboard');
            }

            $errors = $result['errors'];
        } elseif (isset($_GET['expired'])) {
            $data['flash'] = ['type' => 'warning', 'message' => 'Your session expired due to inactivity. Please log in again.'];
        } elseif (isset($_GET['logged_out'])) {
            $data['flash'] = ['type' => 'info', 'message' => 'You have been logged out.'];
        }

        $data['errors'] = $errors;
        $data['old'] = $old;

        render($authController, 'login', $data);
        break;

    case 'logout':
        if ($isPost && $authController->verifyCsrfToken(isset($post['csrf_token']) ? $post['csrf_token'] : null)) {
            $authController->logout();
            redirect('login', ['logged_out' => 1]);
        }

        redirect('home');
        break;

    case 'dashboard':
        if (!$authController->isAuthenticated()) {
            setFlash('warning', 'Please log in to access your dashboard.');
            redirect('login');
        }

        $user = $authController->findById((int) $_SESSION['user_id']);

        if ($user === null) {
            $authController->logout();
            redirect('login');
        }

        render($authController, 'user_space', [
            'pageTitle' => 'Dashboard | AgriEco',
            'currentPage' => 'dashboard',
            'user' => $user,
            'canWrite' => $authController->canWrite(),
        ]);
        break;

    case 'admin':
        if (!$authController->isAuthenticated()) {
            setFlash('warning', 'Please log in to continue.');
            redirect('login');
        }

        $authController->requireRole('admin');

        $roles = ['agriculteur', 'client', 'association', 'admin'];
        $roleCounts = [];

        foreach ($roles as $role) {
            $roleCounts[$role] = $authController->countByRole($role);
        }

        render($authController, 'admin_space', [
            'pageTitle' => 'Admin Panel | AgriEco',
            'currentPage' => 'admin',
            'adminSection' => 'User Management',
            'users' => $authController->getAllUsers(),
            'totalUsers' => $authController->countAllUsers(),
            'roleCounts' => $roleCounts,
            'roles' => $roles,
        ], 'layout_admin');
        break;

    case 'update_access':
        if (!$authController->isAuthenticated()) {
            setFlash('warning', 'Please log in to continue.');
            redirect('login');
        }

        $authController->requireRole('admin');

        if (!$isPost) {
            redirect('admin');
        }

        $result = $authController->updateUserAccess($post);

        if ($result['success']) {
            setFlash('success', 'User access updated successfully.');
        } else {
            setFlash('error', flattenErrors($result['errors']));
        }

        redirect($authController->hasRole('admin') ? 'admin' : 'dashboard');
        break;

    case 'users':
        requireAdminAccess($authController);

        $search = inputString($_GET, 'search');
        $roleFilter = inputString($_GET, 'role');
        $page = (int) (filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1);
        $listData = $userController->listUsers($search, $roleFilter, $page);

        render($authController, 'users/index', [
            'pageTitle' => 'Users | AgriEco',
            'currentPage' => 'users',
            'adminSection' => 'Users',
            'users' => $listData['users'],
            'filteredTotal' => $listData['total'],
            'page' => $listData['page'],
            'perPage' => $listData['perPage'],
            'totalPages' => $listData['totalPages'],
            'search' => $listData['search'],
            'roleFilter' => $listData['role'],
            'totalUsers' => $userController->countAllUsers(),
            'roleCounts' => $userController->getRoleCounts(),
            'roles' => $userController->getRoles(),
        ], 'layout_admin');
        break;

    case 'users_show':
        requireAdminAccess($authController);

        $id = (int) (filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
        $result = $userController->showUser($id);

        if (!$result['success']) {
            setFlash('error', flattenErrors($result['errors']));
            redirect('users');
        }

        $targetUser = $result['user'];
        $deleteCheck = $userController->canDeleteUser($targetUser);

        render($authController, 'users/show', [
            'pageTitle' => 'User Details | AgriEco',
            'currentPage' => 'users',
            'adminSection' => 'User Details',
            'user' => $targetUser,
            'canDelete' => $deleteCheck['allowed'],
            'deleteBlockReason' => $deleteCheck['reason'],
        ], 'layout_admin');
        break;

    case 'users_create':
        requireAdminAccess($authController);

        render($authController, 'users/form', [
            'pageTitle' => 'Add User | AgriEco',
            'currentPage' => 'users',
            'adminSection' => 'Add User',
            'isEdit' => false,
            'roles' => $userController->getRoles(),
            'errors' => [],
            'old' => ['username' => '', 'email' => '', 'role' => 'client', 'can_write' => '1'],
        ], 'layout_admin');
        break;

    case 'users_store':
        requireAdminAccess($authController);

        if (!$isPost) {
            redirect('users_create');
        }

        $result = $userController->createUser($post);

        if ($result['success']) {
            setFlash('success', 'User created successfully.');
            redirect('users');
        }

        render($authController, 'users/form', [
            'pageTitle' => 'Add User | AgriEco',
            'currentPage' => 'users',
            'adminSection' => 'Add User',
            'isEdit' => false,
            'roles' => $userController->getRoles(),
            'errors' => $result['errors'],
            'old' => [
                'username' => inputString($post, 'username'),
                'email' => inputString($post, 'email'),
                'role' => inputString($post, 'role') ?: 'client',
                'can_write' => inputString($post, 'can_write') !== '' ? inputString($post, 'can_write') : '1',
            ],
        ], 'layout_admin');
        break;

    case 'users_edit':
        requireAdminAccess($authController);

        $id = (int) (filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
        $targetUser = $userController->findById($id);

        if ($targetUser === null) {
            setFlash('error', 'User not found.');
            redirect('users');
        }

        $isLastAdmin = $targetUser->getRole() === 'admin' && $userController->countAdmins() <= 1;

        render($authController, 'users/form', [
            'pageTitle' => 'Edit User | AgriEco',
            'currentPage' => 'users',
            'adminSection' => 'Edit User',
            'isEdit' => true,
            'userId' => $id,
            'isLastAdmin' => $isLastAdmin,
            'roles' => $userController->getRoles(),
            'errors' => [],
            'old' => [
                'username' => $targetUser->getUsername(),
                'email' => $targetUser->getEmail(),
                'role' => $targetUser->getRole(),
                'can_write' => $targetUser->getCanWrite() ? '1' : '0',
            ],
        ], 'layout_admin');
        break;

    case 'users_update':
        requireAdminAccess($authController);

        if (!$isPost) {
            redirect('users');
        }

        $id = (int) (filter_var($_GET['id'] ?? ($post['id'] ?? 0), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
        $result = $userController->updateUser($id, $post);

        if ($result['success']) {
            setFlash('success', 'User updated successfully.');
            redirect($authController->hasRole('admin') ? 'users' : 'dashboard');
        }

        $targetUser = $userController->findById($id);
        if ($targetUser === null) {
            setFlash('error', flattenErrors($result['errors']));
            redirect('users');
        }

        $isLastAdmin = $targetUser->getRole() === 'admin' && $userController->countAdmins() <= 1;

        render($authController, 'users/form', [
            'pageTitle' => 'Edit User | AgriEco',
            'currentPage' => 'users',
            'adminSection' => 'Edit User',
            'isEdit' => true,
            'userId' => $id,
            'isLastAdmin' => $isLastAdmin,
            'roles' => $userController->getRoles(),
            'errors' => $result['errors'],
            'old' => [
                'username' => inputString($post, 'username'),
                'email' => inputString($post, 'email'),
                'role' => inputString($post, 'role') ?: $targetUser->getRole(),
                'can_write' => inputString($post, 'can_write') !== '' ? inputString($post, 'can_write') : ($targetUser->getCanWrite() ? '1' : '0'),
            ],
        ], 'layout_admin');
        break;

    case 'users_delete':
        requireAdminAccess($authController);

        $id = (int) (filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
        $targetUser = $userController->findById($id);

        if ($targetUser === null) {
            setFlash('error', 'User not found.');
            redirect('users');
        }

        $deleteCheck = $userController->canDeleteUser($targetUser);

        render($authController, 'users/delete', [
            'pageTitle' => 'Delete User | AgriEco',
            'currentPage' => 'users',
            'adminSection' => 'Delete User',
            'user' => $targetUser,
            'canDelete' => $deleteCheck['allowed'],
            'deleteBlockReason' => $deleteCheck['reason'],
        ], 'layout_admin');
        break;

    case 'users_destroy':
        requireAdminAccess($authController);

        if (!$isPost) {
            redirect('users');
        }

        $id = (int) (filter_var($_GET['id'] ?? ($post['id'] ?? 0), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
        $result = $userController->deleteUser($id, $post);

        if ($result['success']) {
            setFlash('success', 'User deleted successfully.');
        } else {
            setFlash('error', flattenErrors($result['errors']));
        }

        redirect('users');
        break;

    case 'users_toggle_status':
        requireAdminAccess($authController);

        if (!$isPost) {
            redirect('users');
        }

        $id = (int) (filter_var($post['user_id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0);
        $result = $userController->toggleStatus($id, $post);

        if ($result['success']) {
            setFlash('success', 'User status updated successfully.');
        } else {
            setFlash('error', flattenErrors($result['errors']));
        }

        if (inputString($post, 'redirect_to') === 'show' && $id > 0) {
            redirect('users_show', ['id' => $id]);
        }

        $redirectParams = [];
        $search = inputString($post, 'search');
        $role = inputString($post, 'role');
        $page = (int) (filter_var($post['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1);

        if ($search !== '') {
            $redirectParams['search'] = $search;
        }
        if ($role !== '') {
            $redirectParams['role'] = $role;
        }
        if ($page > 1) {
            $redirectParams['page'] = $page;
        }

        redirect('users', $redirectParams);
        break;

    default:
        http_response_code(404);
        render($authController, 'errors/404', [
            'pageTitle' => 'Page not found | AgriEco',
            'currentPage' => '',
        ]);
        break;
}