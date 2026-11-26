/**
 * validation.js : contrôle de saisie côté navigateur, réutilisable par tous les modules d'AgriEco.
 *
 * Aucune validation HTML5 : le formulaire a l'attribut novalidate, les champs sont en type="text"
 * (sauf type="password" et type="file"). Les règles sont écrites dans un attribut data-validate.
 *
 * Utilisation dans une vue :
 *   <form method="post" action="..." class="js-validate" novalidate>
 *     <input type="text" name="email" data-label="L'email" data-validate="requis|email">
 *     <input type="text" name="prix"  data-label="Le prix"  data-validate="requis|nombrePositifOuNul">
 *   </form>
 *   <script src="public/js/validation.js"></script> en bas de la page
 *
 * Règles disponibles (séparées par |) :
 *   requis, longueur:min:max, email, lettres, identifiant, telephone, nombrePositif, nombrePositifOuNul, entierPositif,
 *   date, dateNonPassee, motDePasse, identique:autreChamp, dansListe:a,b,c, fichier:2 (taille max en Mo)
 *
 * Les messages sont les mêmes que ceux de Validator.php : le serveur revérifie toujours,
 * car le JavaScript peut être désactivé ou contourné.
 */
document.addEventListener('DOMContentLoaded', function () {

    /** Date du jour au format AAAA-MM-JJ (padStart : 2 chiffres pour le mois et le jour) */
    function aujourdhui() {
        const d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    /** Vraie date AAAA-MM-JJ (refuse le 31 février) */
    function estDateValide(texte) {
        const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(texte);
        if (!m) return false;
        const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
        return d.getFullYear() === Number(m[1]) && d.getMonth() === Number(m[2]) - 1 && d.getDate() === Number(m[3]);
    }

    /** Nombre (point ou virgule accepté) */
    function enNombre(texte) {
        const t = texte.replace(',', '.');
        return t !== '' && !isNaN(t) ? Number(t) : NaN;
    }

    /**
     * Teste UNE règle sur UN champ. Renvoie le message d'erreur, ou '' si c'est bon.
     * Comme dans Validator.php : un champ vide n'est testé que par "requis".
     */
    function testerRegle(champ, regle, formulaire) {
        const morceaux = regle.split(':');
        const nom = morceaux[0];
        const libelle = champ.dataset.label || champ.name;
        const valeur = champ.type === 'password' ? champ.value : champ.value.trim();

        if (nom === 'requis') {
            if (champ.type === 'file') return champ.files.length === 0 ? libelle + ' est obligatoire.' : '';
            return valeur === '' ? libelle + ' est obligatoire.' : '';
        }
        if (nom === 'identique') {
            const autre = formulaire.querySelector('[name="' + morceaux[1] + '"]');
            return autre && valeur !== autre.value ? (champ.dataset.message || 'Les deux valeurs ne sont pas identiques.') : '';
        }
        if (nom === 'fichier') {
            if (champ.files.length === 0) return '';
            const fichier = champ.files[0];
            const tailleMax = Number(morceaux[1] || 2);
            if (fichier.size > tailleMax * 1024 * 1024) return libelle + ' ne doit pas dépasser ' + tailleMax + ' Mo.';
            if (!['application/pdf', 'image/jpeg', 'image/png'].includes(fichier.type)) return libelle + ' doit être un fichier PDF, JPG ou PNG.';
            return '';
        }
        if (valeur === '') return '';   // champ facultatif laissé vide

        switch (nom) {
            case 'longueur': {
                const min = Number(morceaux[1]), max = Number(morceaux[2]);
                return valeur.length < min || valeur.length > max ? libelle + ' doit faire entre ' + min + ' et ' + max + ' caractères.' : '';
            }
            case 'email':
                return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valeur) ? '' : libelle + " n'est pas une adresse email valide.";
            case 'lettres':
                return /^[A-Za-zÀ-ÿ '-]+$/.test(valeur) ? '' : libelle + ' ne doit contenir que des lettres.';
            case 'identifiant':
                return /^[A-Za-z0-9_.-]+$/.test(valeur) ? '' : libelle + ' ne peut contenir que des lettres, des chiffres, des points, des tirets et des tirets bas.';
            case 'telephone':
                return /^(\+216|00216)?[2-9][0-9]{7}$/.test(valeur.replace(/ /g, '')) ? '' : libelle + ' doit contenir 8 chiffres (ex. 22 123 456), avec +216 facultatif.';
            case 'nombrePositif':
                return enNombre(valeur) > 0 ? '' : libelle + ' doit être un nombre supérieur à 0.';
            case 'nombrePositifOuNul':
                return enNombre(valeur) >= 0 ? '' : libelle + ' doit être un nombre supérieur ou égal à 0.';
            case 'entierPositif':
                return /^\d+$/.test(valeur) && Number(valeur) > 0 ? '' : libelle + ' doit être un nombre entier supérieur à 0.';
            case 'date':
                return estDateValide(valeur) ? '' : libelle + ' doit être une date valide au format AAAA-MM-JJ.';
            case 'dateNonPassee':
                return valeur >= aujourdhui() ? '' : libelle + ' ne peut pas être dans le passé.';
            case 'motDePasse':
                return valeur.length >= 8 && /[A-Z]/.test(valeur) && /[a-z]/.test(valeur) && /[0-9]/.test(valeur)
                    ? '' : 'Le mot de passe doit faire au moins 8 caractères, avec une majuscule, une minuscule et un chiffre.';
            case 'dansListe':
                return morceaux[1].split(',').includes(valeur) ? '' : libelle + " n'est pas une valeur autorisée.";
            default:
                return '';
        }
    }

    /** Affiche (ou efface) le message sous le champ, avec les classes Bootstrap */
    function afficher(champ, message) {
        let zone = champ.parentNode.querySelector('.invalid-feedback[data-pour="' + champ.name + '"]');
        if (!zone) {
            zone = document.createElement('div');
            zone.className = 'invalid-feedback';
            zone.dataset.pour = champ.name;
            champ.insertAdjacentElement('afterend', zone);
        }
        zone.textContent = message;
        zone.style.display = message ? 'block' : 'none';
        zone.style.color = '#b42318';      // rouge, même sans la feuille de style Bootstrap
        zone.style.fontSize = '0.875rem';
        champ.classList.toggle('is-invalid', message !== '');
    }

    /** Teste toutes les règles d'un champ ; s'arrête à la première erreur (comme le PHP) */
    function validerChamp(champ, formulaire) {
        const regles = champ.dataset.validate.split('|');
        for (const regle of regles) {
            const message = testerRegle(champ, regle, formulaire);
            if (message) {
                afficher(champ, message);
                return false;
            }
        }
        afficher(champ, '');
        return true;
    }

    // Chaque formulaire marqué js-validate est contrôlé à l'envoi
    document.querySelectorAll('form.js-validate').forEach(function (formulaire) {
        formulaire.addEventListener('submit', function (evenement) {
            let toutEstBon = true;
            let premierFaux = null;
            formulaire.querySelectorAll('[data-validate]').forEach(function (champ) {
                if (!validerChamp(champ, formulaire)) {
                    toutEstBon = false;
                    if (!premierFaux) premierFaux = champ;
                }
            });
            if (!toutEstBon) {
                evenement.preventDefault();   // rien n'est envoyé au serveur
                premierFaux.focus();
            }
        });

        // Le message disparaît dès que l'utilisateur corrige le champ
        formulaire.querySelectorAll('[data-validate]').forEach(function (champ) {
            champ.addEventListener('input', function () {
                if (champ.classList.contains('is-invalid')) validerChamp(champ, formulaire);
            });
        });
    });
});
