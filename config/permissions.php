<?php
return [
    'roles' => ['client', 'agriculteur', 'association', 'admin'],

    'home' => [
        'client'      => 'catalogue',
        'agriculteur' => 'espace_agriculteur',
        'association' => 'don_list',
        'admin'       => 'admin',
    ],

    'public' => [
        'home', 'login', 'register', 'logout',
        'catalogue', 'produit_voir',
    ],

    'rules' => [
        'admin'                => ['admin'],
        'admin_*'              => ['admin'],

        'dashboard'            => ['*'],
        'profil'               => ['*'],

        'espace_agriculteur'   => ['agriculteur'],
        'exploitation_*'       => ['agriculteur'],
        'parcelle_*'           => ['agriculteur'],

        'culture_*'            => ['agriculteur'],
        'recolte_*'            => ['agriculteur'],

        'irrigation_*'         => ['agriculteur'],

        'traitement_*'         => ['agriculteur'],
        'score_*'              => ['agriculteur'],

        'produit_add'          => ['agriculteur'],
        'produit_edit'         => ['agriculteur'],
        'produit_delete'       => ['agriculteur'],
        'produit_list'         => ['agriculteur'],
        'commande_*'           => ['client'],
        'avis_*'               => ['client'],

        'don_propose'          => ['agriculteur'],
        'don_suivi'            => ['agriculteur'],
        'don_*'                => ['association'],
    ],

    'menu' => [
        'visiteur'    => [['Catalogue', 'catalogue']],
        'client'      => [['Catalogue', 'catalogue'], ['Mes commandes', 'commande_list'], ['Mon espace', 'dashboard']],
        'agriculteur' => [['Catalogue', 'catalogue'], ['Exploitation', 'exploitation_list'], ['Cultures', 'culture_list'],
                          ['Eau', 'irrigation_list'], ['Traitements', 'traitement_list'], ['Mes produits', 'produit_list'],
                          ['Dons', 'don_suivi']],
        'association' => [['Dons disponibles', 'don_list'], ['Mon espace', 'dashboard']],
        'admin'       => [['Catalogue', 'catalogue'], ['Backoffice', 'admin']],
    ],

    'menu_admin' => [
        'dashboard'  => ['Tableau de bord', 'admin'],
        'users'      => ['Utilisateurs', 'admin_users'],
        'exploit'    => ['Exploitations', 'admin_exploitations'],
        'cultures'   => ['Cultures et récoltes', 'admin_cultures'],
        'eau'        => ['Eau', 'admin_eau'],
        'traitements'=> ['Traitements', 'admin_traitements'],
        'vente'      => ['Vente et avis', 'admin_vente'],
        'dons'       => ['Anti-gaspillage', 'admin_dons'],
    ],
];
