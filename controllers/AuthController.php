
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
}

