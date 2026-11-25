# AgriEco — Plateforme agricole écoresponsable

Projet Technologies Web, 2ᵉ année Génie Informatique, ESPRIT — classe 2A10, année 2026-2027 — équipe **Devline**.

AgriEco relie directement les agriculteurs, les clients et les associations : gestion des exploitations, des cultures, de l'eau et des traitements, vente directe, et lutte contre le gaspillage des invendus.

> Les passages marqués **[À COMPLÉTER]** sont à remplir par chaque membre quand sa partie est fusionnée sur `main`.

---

## 1. Équipe et répartition

| Membre | Partie (module Utilisateurs et sécurité) | Module métier |
|---|---|---|
| Leth Ben Hmida | Fondation MVC et base de données : `config/database.php`, `models/UserModel.php`, routeur `index.php`, table `users` | [À COMPLÉTER] |
| Bedis Arous | Authentification et sessions : `controllers/AuthController.php`, connexion, inscription, déconnexion | [À COMPLÉTER] |
| Koussay Mannai | CRUD utilisateurs (Back Office) : `controllers/UserController.php`, vues d'administration | [À COMPLÉTER] |
| Fares Mehdoui | Rôles et autorisation : `requireRole()`, layouts Front / Back Office, page 403 | [À COMPLÉTER] |
| Yassine Helali | Sécurité : jeton CSRF, audit XSS et SQL, en-têtes HTTP | [À COMPLÉTER] |
| Ismail Khadhraoui | Validation des saisies (`core/Validator.php`, `public/js/validation.js`), README, GitHub Project | Module 6 — Anti-gaspillage |

## 2. Fonctionnalités

### Gestion des utilisateurs
- Table `users` : `id`, `name`, `fam_name`, `phone_num`, `address` (facultative), `mail` (unique), `password` (haché), `role`, `user_type`, `created_at`.
- Deux notions séparées : `role` = droits sur le site (`admin` ou `user`) ; `user_type` = métier (`farmer`, `client`, `association`).
- Inscription : le visiteur choisit son type ; le rôle est toujours `user` (personne ne peut s'inscrire admin).
- Connexion et déconnexion ; mot de passe haché (`password_hash` / `password_verify`).
- [À COMPLÉTER : CRUD des utilisateurs par l'admin, contrôle d'accès par rôle, page 403.]

### Sécurité et sessions (workshop « Sessions et Sécurité PHP »)
- Session configurée avant `session_start()` dans `index.php` : `use_strict_mode`, cookie `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS.
- Expiration après 30 minutes d'inactivité (`$timeout = 1800`) : la session et son cookie sont détruits.
- Connexion PDO avec `ERRMODE_EXCEPTION` et `ATTR_EMULATE_PREPARES = false` : requêtes vraiment préparées contre l'injection SQL.
- [À COMPLÉTER : jeton CSRF, `htmlspecialchars` dans les vues, en-têtes HTTP.]

### Validation des saisies
- Côté navigateur : `public/js/validation.js`, règles déclarées dans l'attribut `data-validate` de chaque champ.
- Côté serveur : classe `core/Validator.php`, mêmes règles et mêmes messages. Le serveur revérifie toujours, car le JavaScript peut être désactivé.
- Aucune validation HTML5 : formulaires en `novalidate`, champs en `type="text"`.

### Modules métier
| # | Module | Responsable | Fonctionnalités principales |
|---|---|---|---|
| 1 | Exploitations | [À COMPLÉTER] | exploitations et parcelles |
| 2 | Cultures | [À COMPLÉTER] | cultures et récoltes par saison |
| 3 | Eau | [À COMPLÉTER] | irrigations et consommation d'eau par parcelle |
| 4 | Traitements | [À COMPLÉTER] | traitements, produits chimiques, note Éco A à E |
| 5 | Vente directe | [À COMPLÉTER] | produits et commandes |
| 6 | Anti-gaspillage | Ismail Khadhraoui | un agriculteur remet en vente ses invendus à prix réduit ou gratuitement ; un client ou une association envoie une demande ; l'agriculteur accepte ou refuse |

## 3. Technologies

| Partie | Technologie |
|---|---|
| Serveur | PHP 8, Apache (XAMPP) |
| Base de données | MySQL / MariaDB, accès par PDO |
| Architecture | MVC (Model, View, Controller), programmation orientée objet |
| Validation | JavaScript (navigateur) + PHP (serveur) |
| Interface | [À COMPLÉTER : templates Front Office et Back Office] |

## 4. Structure du projet

```
agrieco/
├── index.php                 Routeur : configure la session, gère l'expiration, lit ?action= et charge le contrôleur
├── config/
│   └── database.php          Connexion PDO (Singleton : Database::getInstance()->getConnection())
├── database/
│   └── agrieco.sql           Création de la base et de la table users
├── models/
│   └── UserModel.php         Entité Utilisateur (attributs privés, getters / setters, fromArray)
├── controllers/              [À COMPLÉTER] AuthController, UserController, contrôleurs des modules
├── views/                    [À COMPLÉTER] pages Front Office et Back Office
├── core/
│   └── Validator.php         Contrôle de saisie côté serveur, partagé par tous les modules
└── public/
    └── js/validation.js      Contrôle de saisie côté navigateur, partagé par tous les modules
```

### Routes (toutes passent par `index.php?action=...`)

| Action | Contrôleur | Rôle |
|---|---|---|
| `home` | — | page d'accueil |
| `register`, `login`, `logout` | `controllers/AuthController.php` | inscription, connexion, déconnexion |
| `users` | `controllers/UserController.php` | gestion des utilisateurs |
| autre | — | page 404 |

## 5. Installation

1. Installer **XAMPP** et démarrer **Apache** et **MySQL**.
2. Cloner le dépôt dans le dossier `htdocs` :
   ```
   cd C:\xampp\htdocs
   git clone https://github.com/yass-sulekfn/agrieco.git agrieco
   ```
3. Ouvrir `http://localhost/phpmyadmin`, onglet **Importer**, choisir `database/agrieco.sql`, puis **Importer**. Le script crée la base `agrieco` et la table `users`.
4. Vérifier la connexion dans `config/database.php` (par défaut : hôte `localhost`, base `agrieco`, utilisateur `root`, mot de passe vide).

## 6. Exécution

- Site : `http://localhost/agrieco/index.php`
- Inscription : `http://localhost/agrieco/index.php?action=register`
- Connexion : `http://localhost/agrieco/index.php?action=login`

### Comptes de démonstration
[À COMPLÉTER : comptes de test (au moins un admin) une fois l'inscription en place. Le mot de passe d'un compte admin est à changer avant toute mise en ligne.]

## 7. Organisation Git

- `main` reçoit uniquement du code fusionné par Pull Request.
- Une branche par partie ou par module (ex. `feature/auth`, `feature/validation`, `feature/module6-antigaspillage`).
- Petits commits avec un message clair, puis une Pull Request vers `main`, relue avant fusion.
- Les tâches sont suivies dans le **GitHub Project** du dépôt : une carte par tâche, assignée à son responsable.

## 8. Ajouter le contrôle de saisie à un formulaire (pour tous les membres)

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
    <input type="password" name="password_confirm" data-label="La confirmation"
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
$v->requis('password_confirm', 'La confirmation')
  ->identique('password_confirm', 'password', 'Les deux mots de passe ne sont pas identiques.');
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
