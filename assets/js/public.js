/**
 * GasyImmo — JavaScript Public
 * Fichier : assets/js/public.js
 * Validation formulaires, animations, interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    animerCompteurs();
    autoDismissAlertes();
    initValidationRDV();
    initValidationContact();
});

/* ═══════════════════════════════════════════════════
   ANIMATION COMPTEURS (data-compter="N")
═══════════════════════════════════════════════════ */
function animerCompteurs() {
    document.querySelectorAll('[data-compter]').forEach(function (el) {
        var cible    = parseInt(el.dataset.compter, 10);
        var duree    = 1200;
        var debut    = null;

        function etape(timestamp) {
            if (!debut) debut = timestamp;
            var progres = Math.min((timestamp - debut) / duree, 1);
            var facilite = 1 - Math.pow(1 - progres, 3);
            el.textContent = Math.round(facilite * cible);
            if (progres < 1) requestAnimationFrame(etape);
        }

        requestAnimationFrame(etape);
    });
}

/* ═══════════════════════════════════════════════════
   AUTO DISMISS ALERTES PUBLIQUES
═══════════════════════════════════════════════════ */
function autoDismissAlertes() {
    document.querySelectorAll('.pub-alerte, .alerte').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 400);
        }, 5000);
    });
}

/* ═══════════════════════════════════════════════════
   VALIDATION FORMULAIRE RDV (côté client)
   CIN : 12 chiffres | Téléphone : 10 chiffres
═══════════════════════════════════════════════════ */
function initValidationRDV() {
    var form = document.getElementById('formulaire-rdv');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var valide = true;

        // Nettoyer anciennes erreurs
        form.querySelectorAll('.msg-erreur').forEach(function (el) { el.textContent = ''; });
        form.querySelectorAll('.champ-erreur').forEach(function (el) { el.classList.remove('champ-erreur'); });

        // CIN
        var cinInput = form.querySelector('[name="cin"]');
        if (cinInput && !/^\d{12}$/.test(cinInput.value.trim())) {
            afficherErreurChamp(cinInput, 'Le CIN doit contenir exactement 12 chiffres.');
            valide = false;
        }

        // Téléphone
        var telInput = form.querySelector('[name="telephone"]');
        if (telInput && !/^\d{10}$/.test(telInput.value.trim())) {
            afficherErreurChamp(telInput, 'Le téléphone doit contenir exactement 10 chiffres.');
            valide = false;
        }

        // Champs obligatoires
        ['prenom', 'nom', 'bien_id', 'date_visite', 'heure_visite'].forEach(function (nom) {
            var champ = form.querySelector('[name="' + nom + '"]');
            if (champ && !champ.value.trim()) {
                afficherErreurChamp(champ, 'Ce champ est obligatoire.');
                valide = false;
            }
        });

        // Date future
        var dateInput = form.querySelector('[name="date_visite"]');
        if (dateInput && dateInput.value) {
            var dateChoisie = new Date(dateInput.value);
            var aujourd   = new Date();
            aujourd.setHours(0, 0, 0, 0);
            if (dateChoisie < aujourd) {
                afficherErreurChamp(dateInput, 'La date de visite doit être aujourd\'hui ou future.');
                valide = false;
            }
        }

        if (!valide) e.preventDefault();
    });
}

/* ═══════════════════════════════════════════════════
   VALIDATION FORMULAIRE CONTACT (côté client)
═══════════════════════════════════════════════════ */
function initValidationContact() {
    var form = document.getElementById('formulaire-contact');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var valide = true;

        form.querySelectorAll('.msg-erreur').forEach(function (el) { el.textContent = ''; });

        var emailInput = form.querySelector('[name="email"]');
        if (emailInput && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
            afficherErreurChamp(emailInput, 'Adresse email invalide.');
            valide = false;
        }

        ['nom', 'email', 'message'].forEach(function (nom) {
            var champ = form.querySelector('[name="' + nom + '"]');
            if (champ && !champ.value.trim()) {
                afficherErreurChamp(champ, 'Ce champ est obligatoire.');
                valide = false;
            }
        });

        if (!valide) e.preventDefault();
    });
}

/* ═══════════════════════════════════════════════════
   HELPER : afficher une erreur sous un champ
═══════════════════════════════════════════════════ */
function afficherErreurChamp(champ, message) {
    champ.classList.add('champ-erreur');

    // Cherche le conteneur d'erreur suivant
    var suivant = champ.nextElementSibling;
    if (suivant && suivant.classList.contains('msg-erreur')) {
        suivant.textContent = message;
    } else {
        // Crée un span d'erreur
        var span = document.createElement('span');
        span.className = 'msg-erreur';
        span.textContent = message;
        champ.parentNode.insertBefore(span, champ.nextSibling);
    }

    champ.focus();
}
