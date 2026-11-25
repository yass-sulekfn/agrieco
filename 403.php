<?php
http_response_code(403);

ob_start();
?>
<div class="container">
  <div class="alert alert-error">
    <strong>Erreur 403 — Accès refusé</strong>
  </div>
  <h1>Vous n'avez pas les autorisations nécessaires</h1>
  <p>
    Votre profil actuel ne permet pas d'accéder à cette page ou d'effectuer cette action.
  </p>
  <p style="margin-top: 24px;">
    <a class="btn btn-ocre" href="index.php?action=home">Retour à mon espace</a>
  </p>
</div>
<?php
$content = ob_get_clean();
$title   = '403 Accès refusé · AgriEco';
require __DIR__ . '/layout.php';
