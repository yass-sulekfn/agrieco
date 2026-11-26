
<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    public function register()
    {
        $error = "";
        $success = "";

        $old = [
            'name' => '',
            'fam_name' => '',
            'phone_num' => '',
            'address' => '',
            'mail' => '',
            'user_type' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            requireCsrf();

            $name = trim($_POST['name'] ?? '');
            $fam_name = trim($_POST['fam_name'] ?? '');
            $phone_num = trim($_POST['phone_num'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $mail = trim($_POST['mail'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $user_type = $_POST['user_type'] ?? '';

            $old = [
                'name' => $name,
                'fam_name' => $fam_name,
                'phone_num' => $phone_num,
                'address' => $address,
                'mail' => $mail,
                'user_type' => $user_type
            ];

            if (
                $name == "" ||
                $fam_name == "" ||
                $phone_num == "" ||
                $mail == "" ||
                $password == "" ||
                $confirm_password == "" ||
                $user_type == ""
            ) {
                $error = "Please fill in all required fields.";
            } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
            } elseif (strlen($password) < 8) {
                $error = "Password must contain at least 8 characters.";
            } elseif ($password != $confirm_password) {
                $error = "Passwords do not match.";
            } elseif (
                $user_type != "farmer" &&
                $user_type != "client" &&
                $user_type != "association"
            ) {
                $error = "Please choose a valid account type.";
            } else {
                $db = Database::getInstance()->getConnection();

                $sql = "SELECT id FROM users WHERE mail = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$mail]);

                if ($stmt->fetch()) {
                    $error = "This email is already registered.";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $sql = "INSERT INTO users
                            (name, fam_name, phone_num, address, mail, password, role, user_type)
                            VALUES (?, ?, ?, ?, ?, ?, 'user', ?)";

                    $stmt = $db->prepare($sql);

                    $stmt->execute([
                        $name,
                        $fam_name,
                        $phone_num,
                        $address,
                        $mail,
                        $hashedPassword,
                        $user_type
                    ]);

                    $success = "Your account has been created successfully!";

                    $old = [
                        'name' => '',
                        'fam_name' => '',
                        'phone_num' => '',
                        'address' => '',
                        'mail' => '',
                        'user_type' => ''
                    ];
                }
            }
        }

        $csrfToken = getCsrfToken();

        require __DIR__ . '/../views/register.php';
    }

    public function login()
    {
        $error = "";
        $mail = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            requireCsrf();

            $mail = trim($_POST['mail'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($mail == "" || $password == "") {
                $error = "Please enter your email and password.";
            } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
            } else {
                $db = Database::getInstance()->getConnection();

                $sql = "SELECT id, name, fam_name, mail, password FROM users WHERE mail = ? LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([$mail]);

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['mail'] = $user['mail'];
                    $_SESSION['username'] = trim($user['name'] . ' ' . $user['fam_name']);

                    header('Location: index.php?action=home');
                    exit;

                } else {
                    $error = "Incorrect email or password.";
                }
            }
        }

        $csrfToken = getCsrfToken();

        require __DIR__ . '/../views/login.php';
    }

    public function findById(int $id): ?UserModel
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        return UserModel::fromArray($user);
    }

    public function getAllUsers(): array
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("SELECT * FROM users ORDER BY id ASC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            function ($row) {
                return UserModel::fromArray($row);
            },
            $rows
        );
    }

    public function countByRole(string $role): int
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
        $stmt->execute([$role]);

        return (int) $stmt->fetchColumn();
    }

    public function countAllUsers(): int
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->query("SELECT COUNT(*) FROM users");

        return (int) $stmt->fetchColumn();
    }

	public function createUser(array $data): bool
{
    $db = Database::getInstance()->getConnection();

    $name = trim($data['name'] ?? '');
    $fam_name = trim($data['fam_name'] ?? '');
    $phone_num = trim($data['phone_num'] ?? '');
    $address = trim($data['address'] ?? '');
    $mail = trim($data['mail'] ?? '');
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? 'user';
    $user_type = $data['user_type'] ?? null;

    if ($name === '' || $fam_name === '' || $phone_num === '' || $mail === '' || $password === '') {
        throw new InvalidArgumentException("Please fill in all required fields.");
    }

    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Please enter a valid email address.");
    }

    if (strlen($password) < 8) {
        throw new InvalidArgumentException("Password must contain at least 8 characters.");
    }

    if (!in_array($role, ['user', 'admin'], true)) {
        throw new InvalidArgumentException("Invalid user role.");
    }

    if ($user_type !== null && $user_type !== '' && !in_array($user_type, ['farmer', 'client', 'association'], true)) {
        throw new InvalidArgumentException("Invalid account type.");
    }

    if ($this->emailExists($mail)) {
        throw new InvalidArgumentException("This email is already registered.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare(
        "INSERT INTO users (name, fam_name, phone_num, address, mail, password, role, user_type)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    return $stmt->execute([
        $name,
        $fam_name,
        $phone_num,
        $address === '' ? null : $address,
        $mail,
        $hashedPassword,
        $role,
        $user_type === '' ? null : $user_type
    ]);
}

public function updateUser(int $id, array $data): bool
{
    $db = Database::getInstance()->getConnection();

    $name = trim($data['name'] ?? '');
    $fam_name = trim($data['fam_name'] ?? '');
    $phone_num = trim($data['phone_num'] ?? '');
    $address = trim($data['address'] ?? '');
    $mail = trim($data['mail'] ?? '');
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? '';
    $user_type = $data['user_type'] ?? null;

    if ($id <= 0 || $name === '' || $fam_name === '' || $phone_num === '' || $mail === '') {
        throw new InvalidArgumentException("Please fill in all required fields.");
    }

    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Please enter a valid email address.");
    }

    if (!in_array($role, ['user', 'admin'], true)) {
        throw new InvalidArgumentException("Invalid user role.");
    }

    if ($user_type !== null && $user_type !== '' && !in_array($user_type, ['farmer', 'client', 'association'], true)) {
        throw new InvalidArgumentException("Invalid account type.");
    }

    if ($this->emailExists($mail, $id)) {
        throw new InvalidArgumentException("This email is already used by another account.");
    }

    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $oldRole = $stmt->fetchColumn();

    if ($oldRole === false) {
        throw new InvalidArgumentException("User not found.");
    }

    if ($oldRole === 'admin' && $role !== 'admin' && $this->countByRole('admin') <= 1) {
        throw new InvalidArgumentException("You cannot remove the last administrator.");
    }

    if ($password !== '' && strlen($password) < 8) {
        throw new InvalidArgumentException("Password must contain at least 8 characters.");
    }

    if ($password !== '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare(
            "UPDATE users
             SET name = ?, fam_name = ?, phone_num = ?, address = ?, mail = ?,
                 password = ?, role = ?, user_type = ?
             WHERE id = ?"
        );

        return $stmt->execute([
            $name,
            $fam_name,
            $phone_num,
            $address === '' ? null : $address,
            $mail,
            $hashedPassword,
            $role,
            $user_type === '' ? null : $user_type,
            $id
        ]);
    }

    $stmt = $db->prepare(
        "UPDATE users
         SET name = ?, fam_name = ?, phone_num = ?, address = ?, mail = ?,
             role = ?, user_type = ?
         WHERE id = ?"
    );

    return $stmt->execute([
        $name,
        $fam_name,
        $phone_num,
        $address === '' ? null : $address,
        $mail,
        $role,
        $user_type === '' ? null : $user_type,
        $id
    ]);
}

public function deleteUser(int $id): bool
{
    $db = Database::getInstance()->getConnection();

    if ($id <= 0) {
        throw new InvalidArgumentException("Invalid user ID.");
    }

    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $role = $stmt->fetchColumn();

    if ($role === false) {
        throw new InvalidArgumentException("User not found.");
    }

    if ($role === 'admin' && $this->countByRole('admin') <= 1) {
        throw new InvalidArgumentException("You cannot delete the last administrator.");
    }

    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");

    return $stmt->execute([$id]);
}

private function emailExists(string $mail, ?int $excludeId = null): bool
{
    $db = Database::getInstance()->getConnection();

    if ($excludeId === null) {
        $stmt = $db->prepare("SELECT id FROM users WHERE mail = ?");
        $stmt->execute([$mail]);
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE mail = ? AND id != ?");
        $stmt->execute([$mail, $excludeId]);
    }

    return $stmt->fetch() !== false;
}
}

