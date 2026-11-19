# AgriEco

*Du champ au client, sans intermédiaire.*

PHP MVC web platform connecting farmers, clients and associations directly. Built with plain PHP 8+, PDO and MySQL, running on XAMPP.

## Requirements

- XAMPP with PHP 8.0 or later, Apache and MySQL/MariaDB
- Git

## Setup on XAMPP

1. Clone the repository into the XAMPP web root so the path is `C:\xampp\htdocs\agrieco`:

   ```
   cd C:\xampp\htdocs
   git clone <repository-url> agrieco
   ```

2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin`, click **Import**, choose `sql/agrieco.sql` and run it. This creates the `agrieco` database, the `users` table and the default admin account.
4. Check `config/database.php`. It defaults to host `localhost`, user `root` and an empty password, which is the XAMPP default.
5. Open `http://localhost/agrieco/index.php`.

## Default admin account

| Field    | Value            |
|----------|------------------|
| Email    | admin@agrieco.tn |
| Password | Admin@1234       |

Change this password before any deployment outside your local machine.

## Routes

All requests go through `index.php?action=<name>`.

| Action          | Method    | Access        | Description                           |
|-----------------|-----------|---------------|---------------------------------------|
| `home`          | GET       | Public        | Landing page                          |
| `register`      | GET, POST | Guests        | Sign up as client, farmer, association |
| `login`         | GET, POST | Guests        | Sign in                               |
| `logout`        | POST      | Authenticated | Sign out (CSRF protected)             |
| `dashboard`     | GET       | Authenticated | Personal space (`views/user_space.php`) |
| `admin`         | GET       | Admin         | User management (`views/admin_space.php`) |
| `update_access` | POST      | Admin         | Update a user's role and write access |
| any other value | any       | Public        | 404                                   |

Views still to be added by the team: `views/home.php` and `views/errors/404.php`.

## Security controls

- PDO prepared statements everywhere, with `ATTR_EMULATE_PREPARES` disabled
- Passwords hashed with `password_hash()` and checked with `password_verify()`
- CSRF tokens generated with `random_bytes(32)` and compared with `hash_equals()` on every state-changing form, including logout
- Session ID regenerated on login, strict session mode, `HttpOnly` and `SameSite=Lax` cookies, `Secure` flag on HTTPS
- 1800-second inactivity timeout
- Security headers: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy`
- Output escaped with `htmlspecialchars()` in every view
- Generic login error message, with a dummy hash check for unknown emails to even out timing
- Public signup restricted to `client`, `agriculteur` and `association`; `admin` can only be granted by an admin
- Role and write-access guards returning HTTP 403: `requireRole()` and `requireWriteAccess()`

## Project structure

```
agrieco/
├── index.php
├── assets/css/style.css
├── config/database.php
├── controllers/
├── models/
├── sql/agrieco.sql
└── views/
    ├── layout.php
    ├── layout_admin.php
    └── partials/
```

## MVC conventions for Modules 1 to 6

1. **Model** in `models/<Name>Model.php`: private typed properties, typed getters and setters, and a `fromArray(array $row): self` factory.
2. **Controller** in `controllers/<Name>Controller.php`: constructor sets `$this->db = Database::getConnexion()`. Methods return arrays shaped `['success' => bool, 'errors' => array]`. They never output HTML.
3. **Views** in `views/<module>/`: files only render data. Use `$content` injection through `render()` with `layout.php` for the front office or `layout_admin.php` for the back office.
4. **Routing**: add a `case` to the `switch` in `index.php` and call `render($authController, '<view>', $data, '<layout>')`.
5. **Access control**: call `requireAuth()`, `requireRole('<role>')` or `requireWriteAccess()` at the start of every protected action.
6. **Forms**: POST only for state changes, always with a hidden `csrf_token` field set from `$csrfToken`.
7. **Database**: prepared statements only. Add new tables to `sql/agrieco.sql` with `CREATE TABLE IF NOT EXISTS`.
8. **Styling**: reuse the classes and CSS variables in `assets/css/style.css`. Eco badges use `eco-badge eco-a` to `eco-e`.
9. **Output**: escape everything with `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.
10. **Code style**: no inline comments, `declare(strict_types=1);` in entry files, typed signatures.

## Git workflow

- Never commit directly to `main`.
- Create one branch per module: `feature/module-<n>-<short-name>`.
- Open a pull request and get at least one teammate review before merging.
- Pull and rebase from `main` before opening the pull request.
- Never commit `.env` files, logs or uploaded files.