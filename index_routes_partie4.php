<?php
require_once __DIR__ . '/controllers/AccessControl.php';

AccessControl::syncRoleFromDb($db);

$action = $_GET['action'] ?? 'home';

AccessControl::guard($action);

switch ($action) {

    case 'home':
        if (AccessControl::isAuthenticated()) {
            header('Location: index.php?action=' . AccessControl::homeFor(AccessControl::currentRole()));
        } else {
            header('Location: index.php?action=login');
        }
        exit;

    case 'admin':
    case 'admin_users':
    case 'admin_users_add':
    case 'admin_users_edit':
    case 'admin_users_delete':
    case 'admin_exploitations':
    case 'admin_cultures':
    case 'admin_eau':
    case 'admin_traitements':
    case 'admin_vente':
    case 'admin_dons':
        AccessControl::requireRole('admin');
        break;

    case 'espace_agriculteur':
    case 'exploitation_list':
    case 'exploitation_add':
    case 'exploitation_edit':
    case 'exploitation_delete':
    case 'parcelle_list':
    case 'parcelle_add':
    case 'parcelle_edit':
    case 'parcelle_delete':
    case 'culture_list':
    case 'culture_add':
    case 'recolte_add':
    case 'irrigation_list':
    case 'irrigation_add':
    case 'traitement_list':
    case 'traitement_add':
    case 'score_view':
    case 'produit_list':
    case 'produit_add':
    case 'produit_edit':
    case 'produit_delete':
    case 'don_propose':
    case 'don_suivi':
        AccessControl::requireRole('agriculteur');
        break;

    case 'commande_list':
    case 'commande_add':
    case 'avis_add':
        AccessControl::requireRole('client');
        break;

    case 'don_list':
    case 'don_accept':
    case 'don_confirm':
        AccessControl::requireRole('association');
        break;

    case 'dashboard':
    case 'profil':
        AccessControl::requireAuth();
        break;
}
