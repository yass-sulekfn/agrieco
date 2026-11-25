# AgriEco Security Test Sheet (Part 5)

## 1. SQL Injection Test
- **Target:** Login form / search & filter inputs
- **Payload:** `' OR '1'='1`
- **Expected Result:** Authentication or query rejects input safely using PDO prepared statements (`ATTR_EMULATE_PREPARES => false`); no SQL error stack trace or unauthorized data leak.
- **Status:** [ ] Pending | Screenshot: `screenshots/sqli.png`

## 2. Reflected / Stored XSS Test
- **Target:** User registration / profile / module text fields rendered in views
- **Payload:** `<script>alert('XSS')</script>`
- **Expected Result:** Rendered as literal escaped text via `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` / `e()`; no script execution.
- **Status:** [ ] Pending | Screenshot: `screenshots/xss.png`

## 3. CSRF Protection Test
- **Target:** State-changing `POST` forms (login, registration, user CRUD, module forms)
- **Test Case:** Remove or modify the `csrf_token` hidden field in DevTools before submitting
- **Expected Result:** Request rejected with `403 Forbidden` via `verifyCsrfToken()` / `requireCsrf()`; no database state change.
- **Status:** [ ] Pending | Screenshot: `screenshots/csrf-403.png`

## 4. Access Control (RBAC) & Direct URL Access Test
- **Target:** Protected / admin / role-restricted routes
- **Test Case:** Request a restricted route directly while logged out or with a non-authorized role
- **Expected Result:** Access denied (`403 Forbidden` or authorized role check block).
- **Status:** [ ] Pending | Screenshot: `screenshots/rbac-403.png`

## 5. HTTP Headers & Session Cookie Flags
- **Target:** Browser DevTools -> Network (`index.php` response headers) & Application -> Cookies (`PHPSESSID`)
- **Expected Result:**
  - `X-Frame-Options: DENY`
  - `X-Content-Type-Options: nosniff`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Content-Security-Policy` header present
  - `PHPSESSID` has `HttpOnly` and `SameSite=Lax`
- **Status:** [ ] Pending | Screenshot: `screenshots/headers-cookies.png`

## 6. Cross-Module Audit Checklist
- [ ] All `<form method="POST">` include `<?= csrfField() ?>`
- [ ] All `POST` actions verify CSRF (`verifyCsrfToken()` / `requireCsrf()`)
- [ ] All dynamic view outputs use `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` or `e(...)`
- [ ] All SQL queries use prepared statements (`prepare` + `execute`) with zero raw string concatenation