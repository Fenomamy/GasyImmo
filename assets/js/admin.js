/**
 * GasyImmo — JavaScript Administration
 * Fichier : assets/js/admin.js
 * Graphiques natifs, interactions UI, modals
 */

document.addEventListener('DOMContentLoaded', function () {
    dessinerGraphiquesBarres();
    dessinerGraphiquesDonut();
    initAperçuPhoto();
    initConfirmationSuppression();
    autoDismissAlertes();
    initModals();
    initRechercheTable();
});

/* ═══════════════════════════════════════════════════
   GRAPHIQUES EN BARRES
   Usage HTML :
   <div data-graphique-barres
        data-valeurs="12,8,5"
        data-etiquettes="Jan,Fev,Mar"
        data-couleurs="#2d9a55,#4ade80,#1a6b3a">
   </div>
═══════════════════════════════════════════════════ */
function dessinerGraphiquesBarres() {
    document.querySelectorAll('[data-graphique-barres]').forEach(function (conteneur) {
        var valeurs    = (conteneur.dataset.valeurs    || '0').split(',').map(Number);
        var etiquettes = (conteneur.dataset.etiquettes || '').split(',');
        var couleurs   = (conteneur.dataset.couleurs   || '#2d9a55').split(',');
        var max        = Math.max.apply(null, valeurs) || 1;
        var hauteurMax = 148;

        conteneur.innerHTML = '';

        var zone = document.createElement('div');
        zone.className = 'graphique-barres';

        valeurs.forEach(function (val, i) {
            var px     = Math.max((val / max) * hauteurMax, 4);
            var couleur = couleurs[i % couleurs.length] || '#2d9a55';
            var label   = etiquettes[i] || ('Item ' + (i + 1));

            var item = document.createElement('div');
            item.className = 'barre-item';

            var barre = document.createElement('div');
            barre.className = 'barre-remplissage';
            barre.style.cssText = 'height:' + px + 'px;background:' + couleur + ';';

            var info = document.createElement('span');
            info.className = 'barre-info';
            info.textContent = val;
            barre.appendChild(info);

            var etiq = document.createElement('div');
            etiq.className = 'barre-etiquette';
            etiq.textContent = label;

            item.appendChild(barre);
            item.appendChild(etiq);
            zone.appendChild(item);
        });

        conteneur.appendChild(zone);

        // Animation d'entrée
        setTimeout(function () {
            zone.querySelectorAll('.barre-remplissage').forEach(function (b) {
                var h = b.style.height;
                b.style.height = '4px';
                b.style.transition = 'height .6s cubic-bezier(.34,1.56,.64,1)';
                requestAnimationFrame(function () { b.style.height = h; });
            });
        }, 80);
    });
}

/* ═══════════════════════════════════════════════════
   GRAPHIQUES DONUT SVG
   Usage HTML :
   <div data-graphique-donut
        data-segments="40,30,20,10"
        data-couleurs="#16a34a,#d97706,#dc2626,#0891b2"
        data-etiquettes="Disponible,Réservé,Vendu,Loué"
        data-etiquette-centre="Biens">
   </div>
═══════════════════════════════════════════════════ */
function dessinerGraphiquesDonut() {
    document.querySelectorAll('[data-graphique-donut]').forEach(function (conteneur) {
        var segments    = (conteneur.dataset.segments    || '1').split(',').map(Number);
        var couleurs    = (conteneur.dataset.couleurs    || '#2d9a55').split(',');
        var etiquettes  = (conteneur.dataset.etiquettes  || '').split(',');
        var labelCentre = conteneur.dataset.etiquetteCentre || 'Total';

        var total = segments.reduce(function (a, b) { return a + b; }, 0) || 1;
        var rayon = 50, cx = 65, cy = 65;
        var circonference = 2 * Math.PI * rayon;

        conteneur.innerHTML = '';

        // Enveloppe donut
        var env = document.createElement('div');
        env.className = 'donut-enveloppe';

        var svgNS = 'http://www.w3.org/2000/svg';
        var svg = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('viewBox', '0 0 130 130');
        svg.setAttribute('class', 'donut-svg');

        // Cercle fond
        var fond = document.createElementNS(svgNS, 'circle');
        fond.setAttribute('cx', cx); fond.setAttribute('cy', cy); fond.setAttribute('r', rayon);
        fond.setAttribute('fill', 'none');
        fond.setAttribute('stroke', '#dde8e2');
        fond.setAttribute('stroke-width', '13');
        svg.appendChild(fond);

        var offset = 0;
        segments.forEach(function (val, i) {
            if (val === 0) return;
            var pct  = val / total;
            var dash = pct * circonference;
            var gap  = circonference - dash;

            var cercle = document.createElementNS(svgNS, 'circle');
            cercle.setAttribute('cx', cx); cercle.setAttribute('cy', cy); cercle.setAttribute('r', rayon);
            cercle.setAttribute('fill', 'none');
            cercle.setAttribute('stroke', couleurs[i % couleurs.length] || '#2d9a55');
            cercle.setAttribute('stroke-width', '13');
            cercle.setAttribute('stroke-dasharray', dash + ' ' + gap);
            cercle.setAttribute('stroke-dashoffset', -(offset * circonference));
            cercle.setAttribute('stroke-linecap', 'butt');
            svg.appendChild(cercle);

            offset += pct;
        });

        env.appendChild(svg);

        // Texte centre
        var centre = document.createElement('div');
        centre.className = 'donut-centre';
        centre.innerHTML = '<span>' + total + '</span><small>' + labelCentre + '</small>';
        env.appendChild(centre);

        conteneur.appendChild(env);

        // Légende
        var legende = document.createElement('div');
        legende.className = 'legende-graphique';

        segments.forEach(function (val, i) {
            var pct  = Math.round((val / total) * 100);
            var item = document.createElement('div');
            item.className = 'legende-item';
            item.innerHTML =
                '<span class="legende-point" style="background:' + (couleurs[i % couleurs.length] || '#ccc') + '"></span>' +
                '<span class="legende-libelle">' + (etiquettes[i] || 'Item ' + (i + 1)) + '</span>' +
                '<span class="legende-valeur">' + val + '</span>' +
                '<span style="color:#7a9e89;font-size:11px;margin-left:3px;">(' + pct + '%)</span>';
            legende.appendChild(item);
        });

        conteneur.appendChild(legende);
    });
}

/* ═══════════════════════════════════════════════════
   APERÇU IMAGE UPLOAD
═══════════════════════════════════════════════════ */
function initAperçuPhoto() {
    var input   = document.getElementById('photo');
    var apercu  = document.getElementById('apercu-photo');
    if (!input || !apercu) return;

    input.addEventListener('change', function () {
        var fichier = this.files[0];
        if (!fichier) return;
        var lecteur = new FileReader();
        lecteur.onload = function (e) {
            apercu.src = e.target.result;
            apercu.style.display = 'block';
        };
        lecteur.readAsDataURL(fichier);
    });
}

/* ═══════════════════════════════════════════════════
   CONFIRMATION SUPPRESSION
   Usage : <a data-confirmer="Message ?">Supprimer</a>
═══════════════════════════════════════════════════ */
function initConfirmationSuppression() {
    document.querySelectorAll('[data-confirmer]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var msg = this.dataset.confirmer || 'Confirmer la suppression ?';
            if (!confirm(msg)) e.preventDefault();
        });
    });
}

/* ═══════════════════════════════════════════════════
   AUTO DISMISS ALERTES (4 secondes)
═══════════════════════════════════════════════════ */
function autoDismissAlertes() {
    document.querySelectorAll('.alerte').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity    = '0';
            setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 400);
        }, 4500);
    });
}

/* ═══════════════════════════════════════════════════
   MODALS
   Ouvrir : data-modal-ouvrir="id-modal"
   Fermer : data-modal-fermer="id-modal"
═══════════════════════════════════════════════════ */
function initModals() {
    document.querySelectorAll('[data-modal-ouvrir]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id  = this.dataset.modalOuvrir;
            var el  = document.getElementById(id);
            if (el) el.classList.add('ouvert');
        });
    });

    document.querySelectorAll('[data-modal-fermer]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.modalFermer;
            var el = document.getElementById(id);
            if (el) el.classList.remove('ouvert');
        });
    });

    document.querySelectorAll('.fond-modal').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('ouvert');
        });
    });
}

/* ═══════════════════════════════════════════════════
   RECHERCHE CLIENT-SIDE DANS TABLE
   Usage : filterTable('id-input', 'id-table')
═══════════════════════════════════════════════════ */
function initRechercheTable() {
    var input = document.getElementById('recherche-rapide');
    var table = document.getElementById('table-principale');
    if (!input || !table) return;

    input.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        table.querySelectorAll('tbody tr').forEach(function (tr) {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

/* ═══════════════════════════════════════════════════
   UTILITAIRE : remplir modal édition client
═══════════════════════════════════════════════════ */
function ouvrirModalEditionClient(client) {
    document.getElementById('edit-id').value        = client.id;
    document.getElementById('edit-prenom').value    = client.prenom;
    document.getElementById('edit-nom').value       = client.nom;
    document.getElementById('edit-cin').value       = client.cin;
    document.getElementById('edit-telephone').value = client.telephone || '';
    document.getElementById('edit-email').value     = client.email    || '';
    document.getElementById('edit-adresse').value   = client.adresse  || '';
    document.getElementById('modal-edition-client').classList.add('ouvert');
}
