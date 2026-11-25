<?php
require_once __DIR__ . '/../controllers/AccessControl.php';

$e = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$profile      = AccessControl::profile();
$menu         = AccessControl::menu();
$activeAction = $activeAction ?? ($_GET['action'] ?? '');
$roleLabels   = [
    'client' => 'Client', 'agriculteur' => 'Agriculteur',
    'association' => 'Association', 'admin' => 'Administrateur',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $e($title ?? 'AgriEco') ?></title>
<style>
:root {
  --foret: #1F3A2B;
  --feuille: #3E8E5A;
  --creme: #F6F4EC;
  --sauge: #90B66D;
  --moutarde: #E6C341;
  --ocre: #DFA13A;
  --brique: #A93926;
  --ligne: #dcd8c8;
  --texte: #1F3A2B;
  --texte-doux: #5b6b60;
  --serif: Rockwell, "Roboto Slab", Georgia, "Times New Roman", serif;
  --sans: Nunito, "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
}
* { box-sizing: border-box; }
body { margin: 0; font-family: var(--sans); background: var(--creme); color: var(--texte); line-height: 1.55; }
a { color: var(--feuille); }

.topnav { background: #fff; border-bottom: 1px solid var(--ligne); }
.topnav .inner { max-width: 1100px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; gap: 28px; flex-wrap: wrap; }
.brand { font-family: var(--serif); font-size: 24px; font-weight: 700; color: var(--foret); text-decoration: none; letter-spacing: -.3px; }
.links { display: flex; gap: 4px; flex-wrap: wrap; flex: 1; }
.links a { text-decoration: none; color: var(--texte); font-size: 15px; padding: 7px 12px; border-radius: 8px; }
.links a:hover { background: var(--creme); }
.links a[aria-current="page"] { background: var(--foret); color: #fff; }
.account { display: flex; align-items: center; gap: 12px; font-size: 14px; }
.who { color: var(--texte-doux); }
.who strong { color: var(--texte); }
.btn { display: inline-block; text-decoration: none; border: 0; border-radius: 8px; padding: 8px 16px; font: 600 14px var(--sans); cursor: pointer; }
.btn-ocre { background: var(--ocre); color: var(--foret); }
.btn-ocre:hover { background: #cf9230; }
.btn-ghost { background: transparent; color: var(--foret); border: 1px solid var(--foret); }
.btn-ghost:hover { background: var(--foret); color: #fff; }
a:focus-visible, .btn:focus-visible, button:focus-visible, input:focus-visible { outline: 3px solid var(--moutarde); outline-offset: 2px; }

.badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.badge-client      { background: #e4efe7; color: var(--feuille); }
.badge-agriculteur { background: #eef3e2; color: #4d6d2a; }
.badge-association { background: #fbf1d4; color: #8a6a12; }
.badge-admin       { background: #f4dcd7; color: var(--brique); }

.eco { display: inline-block; min-width: 46px; text-align: center; padding: 2px 10px; border-radius: 999px; font: 700 13px var(--serif); color: #fff; }
.eco-a { background: var(--feuille); } .eco-b { background: var(--sauge); }
.eco-c { background: var(--moutarde); color: var(--foret); }
.eco-d { background: var(--ocre); color: var(--foret); } .eco-e { background: var(--brique); }

main { max-width: 1100px; margin: 0 auto; padding: 32px 24px 56px; }
h1 { font-family: var(--serif); font-size: 28px; margin: 0 0 16px; letter-spacing: -.3px; }
.container { max-width: 480px; margin: 40px auto; background: #fff; border: 1px solid var(--ligne); border-radius: 12px; padding: 32px; }
.container.wide { max-width: 860px; }
label { display: block; font-size: 14px; color: var(--texte-doux); margin: 14px 0 6px; }
input[type=text], input[type=email], input[type=password], input[type=number], input[type=date], select, textarea {
  width: 100%; padding: 10px 12px; border: 1px solid var(--ligne); border-radius: 8px; font: 15px var(--sans); background: #fff; color: var(--texte); }
input:focus, select:focus, textarea:focus { border-color: var(--feuille); }
form button[type=submit] { margin-top: 20px; width: 100%; padding: 11px; background: var(--feuille); color: #fff; border: 0; border-radius: 8px; font: 700 15px var(--sans); cursor: pointer; }
form button[type=submit]:hover { background: var(--foret); }
.alert { padding: 10px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 14px; }
.alert-error { background: #f8e3df; color: var(--brique); border: 1px solid #ecc0b8; }
.alert-success { background: #e4efe7; color: #276b40; border: 1px solid #bcd8c5; }
table { width: 100%; border-collapse: collapse; margin-top: 16px; }
th, td { text-align: left; padding: 10px; border-bottom: 1px solid var(--ligne); font-size: 14px; }
th { color: var(--texte-doux); font-weight: 700; }
footer { border-top: 1px solid var(--ligne); text-align: center; padding: 20px; font-size: 13px; color: var(--texte-doux); }
</style>
</head>
<body>
<header class="topnav">
  <div class="inner">
    <a class="brand" href="index.php?action=home">AgriEco</a>

    <nav class="links" aria-label="Navigation principale">
      <?php foreach ($menu as [$label, $action]): ?>
        <a href="index.php?action=<?= $e($action) ?>"<?= $activeAction === $action ? ' aria-current="page"' : '' ?>><?= $e($label) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="account">
      <?php if ($profile === 'visiteur'): ?>
        <a class="btn btn-ghost" href="index.php?action=register">Créer un compte</a>
        <a class="btn btn-ocre" href="index.php?action=login">Connexion</a>
      <?php else: ?>
        <span class="who">
          <strong><?= $e($_SESSION['username'] ?? '') ?></strong>
          <span class="badge badge-<?= $e($profile) ?>"><?= $e($roleLabels[$profile] ?? $profile) ?></span>
        </span>
        <a class="btn btn-ocre" href="index.php?action=logout">Se déconnecter</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main>
  <?= $content ?>
</main>

<footer>AgriEco · Du champ au client, sans intermédiaire</footer>
</body>
</html>
