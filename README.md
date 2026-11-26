# AgriEco

## Description

AgriEco est une application web développée avec PHP et MySQL. Elle permet de faciliter la gestion des activités agricoles et les échanges entre agriculteurs, clients et associations.

## Technologies utilisées

- HTML
- CSS
- JavaScript
- PHP
- MySQL
- PDO

## Fonctionnalités principales

- Inscription et connexion des utilisateurs.
- Gestion des comptes utilisateurs.
- Gestion des rôles et des autorisations.
- Espace personnel pour les utilisateurs.
- Tableau de bord administrateur.
- Gestion des sessions.
- Protection des formulaires et des données.

## Types d'utilisateurs

- **Administrateur :** accès à l'administration et à la gestion des utilisateurs.
- **Agriculteur :** accès aux fonctionnalités agricoles.
- **Client :** accès aux fonctionnalités destinées aux clients.
- **Association :** accès aux fonctionnalités liées aux dons.

## Sécurité

Le projet utilise :

- Le hachage des mots de passe.
- Les requêtes préparées PDO.
- La validation des formulaires.
- La protection contre les attaques CSRF.
- La protection contre les attaques XSS.
- La gestion sécurisée des sessions.
- Le contrôle des accès selon les rôles.

## Équipe

Projet réalisé dans le cadre d'un travail universitaire en groupe.

### Répartition (module Utilisateurs et sécurité)

| Membre | Partie (module Utilisateurs et sécurité) | Module métier |
|---|---|---|
| Leth Ben Hmida | Fondation MVC et base de données : `config/database.php`, `models/UserModel.php`, routeur `index.php`, table `users` | [À COMPLÉTER] |
| Bedis Arous | Authentification et sessions : `controllers/AuthController.php`, connexion, inscription, déconnexion | [À COMPLÉTER] |
| Koussay Mannai | CRUD utilisateurs (Back Office) : actions `addUser`, `editUser`, `deleteUser`, vues `user_add.php`, `user_edit.php`, `admin_space.php` | [À COMPLÉTER] |
| Fares Mehdoui | Rôles et autorisation : `requireRole()`, layouts Front / Back Office, page 403 | [À COMPLÉTER] |
| Yassine Helali | Sécurité : jeton CSRF, audit XSS et SQL, en-têtes HTTP | [À COMPLÉTER] |
| Ismail Khadhraoui | Validation des saisies (`core/Validator.php`, `public/js/validation.js`), README, GitHub Project | Module 6 — Anti-gaspillage |

## Structure du projet

```
agrieco/
├── index.php              Routeur : session sécurisée, en-têtes HTTP, ?action= → contrôleur
├── config/
│   ├── database.php       Connexion PDO (Singleton)
│   ├── csrf.php           Jeton CSRF et fonction e() (échappement XSS)
│   └── permissions.php    Rôles, droits par action, menus
├── controllers/
│   ├── AuthController.php Inscription, connexion, CRUD des utilisateurs
│   └── AccessControl.php  requireAuth(), requireRole(), page 403
├── models/
│   └── UserModel.php      Entité Utilisateur
├── views/                 Pages (login, register, espaces, layouts, 403)
├── database/
│   └── agrieco.sql        Base agrieco et table users
├── core/
│   └── Validator.php      Contrôle de saisie côté serveur, partagé par tous les modules
└── public/
    └── js/validation.js   Contrôle de saisie côté navigateur, partagé par tous les modules
```

## Installation

1. Installer **XAMPP** et démarrer **Apache** et **MySQL**.
2. Cloner le dépôt dans le dossier `htdocs` :
   ```
   cd C:\xampp\htdocs
   git clone https://github.com/yass-sulekfn/agrieco.git agrieco
   ```
3. Ouvrir `http://localhost/phpmyadmin`, onglet **Importer**, choisir `database/agrieco.sql`, puis **Importer**. Le script crée la base `agrieco` et la table `users`.
4. Vérifier la connexion dans `config/database.php` (par défaut : hôte `localhost`, base `agrieco`, utilisateur `root`, mot de passe vide).

## Organisation Git

- `main` reçoit uniquement du code fusionné par Pull Request.
- Une branche par partie ou par module (ex. `feature/auth`, `feature/validation`, `feature/module6-antigaspillage`).
- Petits commits avec un message clair, puis une Pull Request vers `main`, relue avant fusion.
- Les tâches sont suivies dans le **GitHub Project** du dépôt : une carte par tâche, assignée à son responsable.

## Ajouter le contrôle de saisie à un formulaire (pour tous les membres)

### Règles disponibles

| Règle JS (`data-validate`) | Méthode PHP (`Validator`) | Vérifie |
|---|---|---|
| `requis` | `requis()` | champ non vide |
| `longueur:min:max` | `longueur()` | nombre de caractères |
| `email` | `email()` | adresse email valide |
| `lettres` | `lettres()` | lettres, espaces, apostrophes, tirets |
| `identifiant` | `identifiant()` | lettres sans accent, chiffres, `.` `_` `-` |
| `telephone` | `telephone()` | 8 chiffres tunisiens, `+216` facultatif |
| `nombrePositif` | `nombrePositif()` | nombre > 0 |
| `nombrePositifOuNul` | `nombrePositifOuNul()` | nombre ≥ 0 |
| `entierPositif` | `entierPositif()` | entier > 0 |
| `date` | `date()` | vraie date AAAA-MM-JJ |
| `dateNonPassee` | `dateNonPassee()` | aujourd'hui ou plus tard |
| `motDePasse` | `motDePasse()` | 8 caractères, majuscule, minuscule, chiffre |
| `identique:champ` | `identique()` | deux champs égaux (confirmation) |
| `dansListe:a,b,c` | `dansListe()` | valeur autorisée (rôle, type, statut) |
| `fichier:2` | `fichier()` | PDF, JPG ou PNG, 2 Mo maximum (formulaire en `enctype="multipart/form-data"`) |

Un champ vide n'est testé que par `requis` : un champ facultatif laissé vide n'est pas une erreur.

### Exemple : le formulaire d'inscription (table `users`)

**Vue** (`views/register.php`) :
```html
<form method="post" action="index.php?action=register" class="js-validate" novalidate>
    <input type="text" name="name" data-label="Le prénom" data-validate="requis|longueur:2:100|lettres">
    <input type="text" name="fam_name" data-label="Le nom" data-validate="requis|longueur:2:100|lettres">
    <input type="text" name="phone_num" data-label="Le téléphone" data-validate="requis|telephone">
    <input type="text" name="address" data-label="L'adresse" data-validate="longueur:5:255">
    <input type="text" name="mail" data-label="L'email" data-validate="requis|longueur:5:150|email">
    <input type="password" name="password" data-label="Le mot de passe" data-validate="requis|motDePasse">
    <input type="password" name="confirm_password" data-label="La confirmation"
           data-message="Les deux mots de passe ne sont pas identiques." data-validate="requis|identique:password">
    <select name="user_type" data-label="Le type de compte" data-validate="requis|dansListe:farmer,client,association">
        <option value="">Choisir…</option>
        <option value="farmer">Agriculteur</option>
        <option value="client">Client</option>
        <option value="association">Association</option>
    </select>
    <button type="submit">Créer mon compte</button>
</form>
<script src="public/js/validation.js"></script>
```

**Contrôleur** (`AuthController`, avant l'`INSERT`) :
```php
require_once __DIR__ . '/../core/Validator.php';

$v = new Validator($_POST);
$v->requis('name', 'Le prénom')->longueur('name', 2, 100, 'Le prénom')->lettres('name', 'Le prénom');
$v->requis('fam_name', 'Le nom')->longueur('fam_name', 2, 100, 'Le nom')->lettres('fam_name', 'Le nom');
$v->requis('phone_num', 'Le téléphone')->telephone('phone_num', 'Le téléphone');
$v->longueur('address', 5, 255, "L'adresse");   // facultative
$v->requis('mail', "L'email")->longueur('mail', 5, 150, "L'email")->email('mail', "L'email");
$v->requis('password', 'Le mot de passe')->motDePasse('password');
$v->requis('confirm_password', 'La confirmation')
  ->identique('confirm_password', 'password', 'Les deux mots de passe ne sont pas identiques.');
$v->requis('user_type', 'Le type de compte')
  ->dansListe('user_type', ['farmer', 'client', 'association'], 'Le type de compte');

if ($v->estValide()) {
    // vérifier que le mail n'existe pas déjà, puis INSERT avec password_hash() et role = 'user'
} else {
    $erreurs = $v->getErreurs();   // à afficher dans la vue avec htmlspecialchars()
}
```
Le `role` n'est jamais lu dans le formulaire d'inscription : il vaut toujours `user`.

### Connexion
```html
<input type="text" name="mail" data-label="L'email" data-validate="requis|email">
<input type="password" name="password" data-label="Le mot de passe" data-validate="requis">
```
```php
$v = new Validator($_POST);
$v->requis('mail', "L'email");
$v->requis('password', 'Le mot de passe');
```

### Formulaire admin (changer le rôle ou le type)
```php
$v->dansListe('role', ['admin', 'user'], 'Le rôle');
$v->dansListe('user_type', ['farmer', 'client', 'association'], 'Le type de compte');
```
