<?php
/**
 * Validator : contrôle de saisie côté serveur, réutilisable par tous les modules d'AgriEco.
 *
 * Principe : on crée un Validator avec les données reçues ($_POST), on enchaîne les règles
 * champ par champ, puis on regarde s'il y a des erreurs.
 * Le contrôle JavaScript (validation.js) applique les mêmes règles avec les mêmes messages,
 * mais il peut être désactivé : le serveur revérifie TOUJOURS ici.
 *
 * Exemple dans un contrôleur :
 *     $v = new Validator($_POST);
 *     $v->requis('mail', "L'email")->email('mail', "L'email");
 *     $v->requis('password', 'Le mot de passe')->motDePasse('password');
 *     if (!$v->estValide()) {
 *         $erreurs = $v->getErreurs();   // tableau champ => message, à afficher dans la vue
 *     }
 */
class Validator
{
    private $donnees;          // les données reçues du formulaire (souvent $_POST)
    private $erreurs = [];     // champ => premier message d'erreur trouvé

    public function __construct(array $donnees)
    {
        $this->donnees = $donnees;
    }

    /** Valeur d'un champ, sans les espaces au début et à la fin ('' si le champ n'existe pas) */
    public function valeur($champ)
    {
        $valeur = $this->donnees[$champ] ?? '';
        return is_string($valeur) ? trim($valeur) : $valeur;
    }

    /** Enregistre une erreur, seulement la première pour chaque champ */
    private function ajouterErreur($champ, $message)
    {
        if (!isset($this->erreurs[$champ])) {
            $this->erreurs[$champ] = $message;
        }
    }

    /** Vrai si le champ a déjà une erreur, ou s'il est vide : les règles suivantes ne testent rien */
    private function aIgnorer($champ)
    {
        return isset($this->erreurs[$champ]) || $this->valeur($champ) === '';
    }

    // ---------------------------------------------------------------- Règles

    /** Le champ ne doit pas être vide */
    public function requis($champ, $libelle)
    {
        if ($this->valeur($champ) === '') {
            $this->ajouterErreur($champ, "$libelle est obligatoire.");
        }
        return $this;
    }

    /** Nombre de caractères entre $min et $max */
    public function longueur($champ, $min, $max, $libelle)
    {
        if (!$this->aIgnorer($champ)) {
            $n = mb_strlen($this->valeur($champ));
            if ($n < $min || $n > $max) {
                $this->ajouterErreur($champ, "$libelle doit faire entre $min et $max caractères.");
            }
        }
        return $this;
    }

    /** Adresse email valide */
    public function email($champ, $libelle)
    {
        if (!$this->aIgnorer($champ) && !filter_var($this->valeur($champ), FILTER_VALIDATE_EMAIL)) {
            $this->ajouterErreur($champ, "$libelle n'est pas une adresse email valide.");
        }
        return $this;
    }

    /** Le champ respecte une expression régulière (ex. lettres seulement) */
    public function regex($champ, $motif, $message)
    {
        if (!$this->aIgnorer($champ) && !preg_match($motif, $this->valeur($champ))) {
            $this->ajouterErreur($champ, $message);
        }
        return $this;
    }

    /** Lettres seulement (accents, espaces, apostrophes et tirets acceptés) : nom de produit, ville... */
    public function lettres($champ, $libelle)
    {
        return $this->regex($champ, "/^[\p{L} '-]+$/u", "$libelle ne doit contenir que des lettres.");
    }

    /** Identifiant (nom d'utilisateur) : lettres sans accent, chiffres, points, tirets, tirets bas */
    public function identifiant($champ, $libelle)
    {
        return $this->regex($champ, '/^[A-Za-z0-9_.-]+$/', "$libelle ne peut contenir que des lettres, des chiffres, des points, des tirets et des tirets bas.");
    }

    /** Numéro de téléphone tunisien : 8 chiffres, avec +216 ou 00216 facultatif (espaces acceptés) */
    public function telephone($champ, $libelle)
    {
        if (!$this->aIgnorer($champ)) {
            $numero = str_replace(' ', '', $this->valeur($champ));
            if (!preg_match('/^(\+216|00216)?[2-9][0-9]{7}$/', $numero)) {
                $this->ajouterErreur($champ, "$libelle doit contenir 8 chiffres (ex. 22 123 456), avec +216 facultatif.");
            }
        }
        return $this;
    }

    /** Nombre (entier ou décimal, point ou virgule) strictement supérieur à 0 */
    public function nombrePositif($champ, $libelle)
    {
        if (!$this->aIgnorer($champ)) {
            $valeur = str_replace(',', '.', $this->valeur($champ));
            if (!is_numeric($valeur) || $valeur <= 0) {
                $this->ajouterErreur($champ, "$libelle doit être un nombre supérieur à 0.");
            }
        }
        return $this;
    }

    /** Nombre supérieur ou égal à 0 (ex. un prix qui peut être gratuit) */
    public function nombrePositifOuNul($champ, $libelle)
    {
        if (!$this->aIgnorer($champ)) {
            $valeur = str_replace(',', '.', $this->valeur($champ));
            if (!is_numeric($valeur) || $valeur < 0) {
                $this->ajouterErreur($champ, "$libelle doit être un nombre supérieur ou égal à 0.");
            }
        }
        return $this;
    }

    /** Nombre entier strictement supérieur à 0 (ex. une quantité en pièces) */
    public function entierPositif($champ, $libelle)
    {
        if (!$this->aIgnorer($champ)) {
            $valeur = (string) $this->valeur($champ);
            if (!ctype_digit($valeur) || (int) $valeur <= 0) {
                $this->ajouterErreur($champ, "$libelle doit être un nombre entier supérieur à 0.");
            }
        }
        return $this;
    }

    /** Vraie date au format AAAA-MM-JJ (checkdate refuse le 31 février) */
    public function date($champ, $libelle)
    {
        if (!$this->aIgnorer($champ)) {
            $ok = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $this->valeur($champ), $morceaux)
                && checkdate((int) $morceaux[2], (int) $morceaux[3], (int) $morceaux[1]);
            if (!$ok) {
                $this->ajouterErreur($champ, "$libelle doit être une date valide au format AAAA-MM-JJ.");
            }
        }
        return $this;
    }

    /** Date aujourd'hui ou plus tard (à utiliser après date()) */
    public function dateNonPassee($champ, $libelle)
    {
        if (!$this->aIgnorer($champ) && $this->valeur($champ) < date('Y-m-d')) {
            $this->ajouterErreur($champ, "$libelle ne peut pas être dans le passé.");
        }
        return $this;
    }

    /** Mot de passe fort : 8 caractères minimum, une majuscule, une minuscule, un chiffre */
    public function motDePasse($champ)
    {
        if (!$this->aIgnorer($champ)) {
            $mdp = $this->donnees[$champ];   // pas de trim sur un mot de passe
            if (strlen($mdp) < 8 || !preg_match('/[A-Z]/', $mdp) || !preg_match('/[a-z]/', $mdp) || !preg_match('/[0-9]/', $mdp)) {
                $this->ajouterErreur($champ, 'Le mot de passe doit faire au moins 8 caractères, avec une majuscule, une minuscule et un chiffre.');
            }
        }
        return $this;
    }

    /** Deux champs identiques (ex. confirmation du mot de passe) */
    public function identique($champ, $autreChamp, $message)
    {
        if (($this->donnees[$champ] ?? '') !== ($this->donnees[$autreChamp] ?? '')) {
            $this->ajouterErreur($champ, $message);
        }
        return $this;
    }

    /** La valeur fait partie d'une liste autorisée (ex. un rôle, un statut) */
    public function dansListe($champ, array $valeursAutorisees, $libelle)
    {
        if (!in_array($this->valeur($champ), $valeursAutorisees, true)) {
            $this->ajouterErreur($champ, "$libelle n'est pas une valeur autorisée.");
        }
        return $this;
    }

    /**
     * Fichier envoyé (justificatif) : obligatoire ou non, taille maximale, types autorisés.
     * Le vrai type est lu dans le contenu du fichier (finfo), pas dans son nom.
     */
    public function fichier($champ, $libelle, $obligatoire = true, $tailleMaxMo = 2, array $typesAutorises = ['application/pdf', 'image/jpeg', 'image/png'])
    {
        $fichier = $_FILES[$champ] ?? null;

        if ($fichier === null || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
            if ($obligatoire) {
                $this->ajouterErreur($champ, "$libelle est obligatoire.");
            }
            return $this;
        }
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            $this->ajouterErreur($champ, "L'envoi de $libelle a échoué, veuillez réessayer.");
            return $this;
        }
        if ($fichier['size'] > $tailleMaxMo * 1024 * 1024) {
            $this->ajouterErreur($champ, "$libelle ne doit pas dépasser $tailleMaxMo Mo.");
            return $this;
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (!in_array($finfo->file($fichier['tmp_name']), $typesAutorises, true)) {
            $this->ajouterErreur($champ, "$libelle doit être un fichier PDF, JPG ou PNG.");
        }
        return $this;
    }

    // ---------------------------------------------------------------- Résultat

    /** Vrai s'il n'y a aucune erreur */
    public function estValide()
    {
        return empty($this->erreurs);
    }

    /** Toutes les erreurs : champ => message */
    public function getErreurs()
    {
        return $this->erreurs;
    }

    /** L'erreur d'un champ, ou '' (pratique dans une vue, sous chaque champ) */
    public function getErreur($champ)
    {
        return $this->erreurs[$champ] ?? '';
    }
}
