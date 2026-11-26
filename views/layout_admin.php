
<?php

require_once __DIR__ . '/../controllers/AccessControl.php';
require_once __DIR__ . '/../config/csrf.php';

AccessControl::requireRole('admin');

$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$activePage = $activePage ?? 'dashboard';
$username = isset($user) && is_object($user) ? $user->getUsername() : ($_SESSION['username'] ?? '');
$initial = mb_strtoupper(mb_substr($username, 0, 1, 'UTF-8'), 'UTF-8');

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $e($title ?? 'Administration') ?> · AgriEco</title>
<style>
:root {
  --foret: #1F3A2B;
  --foret-hover: #2b4d3a;
  --feuille: #3E8E5A;
  --creme: #F6F4EC;
  --sauge: #90B66D;
  --moutarde: #E6C341;
  --ocre: #DFA13A;
  --brique: #A93926;
  --ligne: #e1ddcd;
  --texte: #1F3A2B;
  --texte-doux: #5b6b60;
  --serif: Rockwell, "Roboto Slab", Georgia, "Times New Roman", serif;
  --sans: Nunito, "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
}
* { box-sizing: border-box; }
body { margin: 0; font-family: var(--sans); background: var(--creme); color: var(--texte); line-height: 1.5; }
a:focus-visible, button:focus-visible { outline: 3px solid var(--moutarde); outline-offset: 2px; }

.wrapper { display: flex; min-height: 100vh; }

.sidebar { width: 250px; flex-shrink: 0; background: var(--foret); color: #cfdcd3; display: flex; flex-direction: column; padding: 22px 0; }
.sidebar .brand { padding: 0 24px 22px; border-bottom: 1px solid #2f5140; margin-bottom: 14px; }
.sidebar .brand b { display: block; font-family: var(--serif); font-size: 22px; color: #fff; letter-spacing: -.3px; }
.sidebar .brand small { color: var(--ocre); font-weight: 700; font-size: 12px; }
.sidebar nav a { display: block; padding: 10px 24px; margin: 2px 12px; border-radius: 8px; color: #cfdcd3; text-decoration: none; font-size: 15px; }
.sidebar nav a:hover { background: var(--foret-hover); color: #fff; }
.sidebar nav a.active { background: var(--feuille); color: #fff; font-weight: 700; }
.sidebar nav .sep { height: 1px; background: #2f5140; margin: 12px 24px; }
.sidebar-footer { margin-top: auto; padding: 16px 24px 0; border-top: 1px solid #2f5140; }
.sidebar-footer .who { color: #fff; font-weight: 700; }
.sidebar-footer .role { font-size: 13px; color: var(--ocre); }
.sidebar-footer .logout-form { margin-top: 10px; }
.sidebar-footer .logout-form button { padding: 0; border: 0; background: transparent; color: #cfdcd3; font: 14px var(--sans); cursor: pointer; }
.sidebar-footer .logout-form button:hover { color: #fff; }

.main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.topbar { background: #fff; border-bottom: 1px solid var(--ligne); padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; }
.topbar h1 { font-family: var(--serif); font-size: 22px; margin: 0; letter-spacing: -.3px; }
.avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--ocre); color: var(--foret); display: grid; place-items: center; font-weight: 800; }
.content { padding: 28px 32px 48px; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1px solid var(--ligne); border-radius: 12px; padding: 18px 20px; }
.stat-card .label { font-size: 14px; color: var(--texte-doux); }
.stat-card .value { font-family: var(--serif); font-size: 32px; font-weight: 700; color: var(--feuille); }
.stat-card .value.warn { color: var(--ocre); }
.stat-card .value.alert { color: var(--brique); }

.card { background: #fff; border: 1px solid var(--ligne); border-radius: 12px; padding: 22px; margin-bottom: 20px; }
.card h2 { font-family: var(--serif); font-size: 17px; margin: 0 0 12px; }
table { width: 100%; border-collapse: collapse; }
th, td { text-align: left; padding: 10px; border-bottom: 1px solid var(--ligne); font-size: 14px; }
th { color: var(--texte-doux); font-weight: 700; }
tr:last-child td { border-bottom: 0; }

.badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.badge-client { background: #e4efe7; color: var(--feuille); }
.badge-agriculteur { background: #eef3e2; color: #4d6d2a; }
.badge-association { background: #fbf1d4; color: #8a6a12; }
.badge-admin { background: #f4dcd7; color: var(--brique); }
.btn { display: inline-block; border: 0; border-radius: 8px; padding: 7px 14px; font: 700 13px var(--sans); text-decoration: none; cursor: pointer; background: var(--feuille); color: #fff; }
.btn:hover { background: var(--foret); }
.btn-danger { background: var(--brique); }
.btn-danger:hover { background: #8a2c1d; }

@media (max-width: 760px) {
  .wrapper { flex-direction: column; }
  .sidebar { width: 100%; }
  .content, .topbar { padding-left: 16px; padding-right: 16px; }
}
</style>
</head>
<body>
<div class="wrapper">
  <aside class="sidebar">
    <div class="brand"><b>AgriEco</b><small>Administration</small></div>

    <nav aria-label="Navigation du backoffice">
      <?php foreach (AccessControl::adminMenu() as $key => [$label, $action]): ?>
        <a href="index.php?action=<?= $e($action) ?>" class="<?= $activePage === $key ? 'active' : '' ?>"<?= $activePage === $key ? ' aria-current="page"' : '' ?>><?= $e($label) ?></a>
      <?php endforeach; ?>
      <div class="sep"></div>
      <a href="index.php?action=catalogue">Voir le site public</a>
    </nav>

    <div class="sidebar-footer">
      <div class="who"><?= $e($username) ?></div>
      <div class="role">Administrateur</div>

      <form class="logout-form" method="POST" action="index.php?action=logout">
        <?= csrfField() ?>
        <button type="submit">Se déconnecter</button>
      </form>
    </div>
  </aside>

  <div class="main">
    <header class="topbar">
      <h1><?= $e($title ?? 'Tableau de bord') ?></h1>
      <div class="avatar" title="<?= $e($username) ?>"><?= $e($initial) ?></div>
    </header>
    <div class="content">
      <?= $content ?>
    </div>
  </div>
</div>
</body>
</html>

