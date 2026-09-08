<?php
/**
 * GasyImmo — Formulaire de rendez-vous public
 * Fichier : pages/rendez_vous.php
 * Crée automatiquement un client si CIN inexistant
 * Affiche le suivi du statut des RDV du client
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage = 'Demande de rendez-vous';

$bdd     = obtenirBDD();
$erreurs = [];
$succes  = false;

// Biens disponibles pour le formulaire
$biensDisponibles = $bdd->query("
    SELECT id, titre, ville, type
    FROM biens
    WHERE statut IN ('disponible','reserve')
    ORDER BY titre
")->fetchAll();

// Pré-sélection d'un bien via URL (?bien_id=X)
$bienPreselectionne = (int)($_GET['bien_id'] ?? 0);

// ── Suivi RDV via CIN ───────────────────────────────
$suiviRdvs = [];
$cinSuivi  = trim($_GET['cin_suivi'] ?? '');
if ($cinSuivi) {
    if (!validerCIN($cinSuivi)) {
        $erreurs[] = 'CIN de suivi invalide (12 chiffres requis).';
    } else {
        $stmtSuivi = $bdd->prepare("
            SELECT r.*, b.titre AS bien_titre, b.ville AS bien_ville
            FROM rendez_vous r
            JOIN clients c ON r.client_id = c.id
            JOIN biens   b ON r.bien_id   = b.id
            WHERE c.cin = ?
            ORDER BY r.date_visite DESC
        ");
        $stmtSuivi->execute([$cinSuivi]);
        $suiviRdvs = $stmtSuivi->fetchAll();
    }
}

// ── Traitement soumission formulaire ────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['soumettre_rdv'])) {
    $prenom      = trim($_POST['prenom']      ?? '');
    $nom         = trim($_POST['nom']         ?? '');
    $cin         = trim($_POST['cin']         ?? '');
    $telephone   = trim($_POST['telephone']   ?? '');
    $email       = trim($_POST['email']       ?? '');
    $bien_id     = (int)($_POST['bien_id']    ?? 0);
    $date_visite = trim($_POST['date_visite'] ?? '');
    $heure       = trim($_POST['heure_visite'] ?? '');
    $message     = trim($_POST['message']     ?? '');

    // ── Validations serveur ──
    if (!$prenom)               $erreurs[] = 'Le prénom est obligatoire.';
    if (!$nom)                  $erreurs[] = 'Le nom est obligatoire.';
    if (!validerCIN($cin))      $erreurs[] = 'Le CIN doit contenir exactement 12 chiffres.';
    if (!validerTelephone($telephone)) $erreurs[] = 'Le téléphone doit contenir exactement 10 chiffres.';
    if (!$bien_id)              $erreurs[] = 'Veuillez sélectionner un bien.';
    if (!$date_visite)          $erreurs[] = 'La date de visite est obligatoire.';
    if (!$heure)                $erreurs[] = 'L\'heure de visite est obligatoire.';

    // Date future ou aujourd'hui
    if ($date_visite && strtotime($date_visite) < strtotime(date('Y-m-d'))) {
        $erreurs[] = 'La date de visite doit être aujourd\'hui ou dans le futur.';
    }

    // Vérifier que le bien existe et est disponible
    if ($bien_id && empty($erreurs)) {
        $stmtBien = $bdd->prepare("SELECT id, titre, statut FROM biens WHERE id = ?");
        $stmtBien->execute([$bien_id]);
        $bienChoisi = $stmtBien->fetch();
        if (!$bienChoisi) {
            $erreurs[] = 'Ce bien n\'existe pas.';
        } elseif (!in_array($bienChoisi['statut'], ['disponible','reserve'])) {
            $erreurs[] = 'Ce bien n\'est plus disponible pour une visite.';
        }
    }

    if (empty($erreurs)) {
        // ── Logique client : CIN existant → récupérer, sinon créer ──
        $stmtClient = $bdd->prepare("SELECT id FROM clients WHERE cin = ?");
        $stmtClient->execute([$cin]);
        $clientExistant = $stmtClient->fetch();

        if ($clientExistant) {
            $clientId = $clientExistant['id'];
        } else {
            // Crée le nouveau client
            $bdd->prepare("
                INSERT INTO clients (nom, prenom, cin, telephone, email)
                VALUES (?, ?, ?, ?, ?)
            ")->execute([$nom, $prenom, $cin, $telephone, $email]);
            $clientId = (int)$bdd->lastInsertId();
        }

        // Crée le rendez-vous
        $bdd->prepare("
            INSERT INTO rendez_vous (client_id, bien_id, date_visite, heure_visite, message)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([$clientId, $bien_id, $date_visite, $heure, $message]);

        $succes = true;
        // Vider les champs après succès
        $_POST = [];
    }
}

require_once __DIR__ . '/../includes/entete_public.php';
?>

<!-- Bannière -->
<div class="banniere-page">
    <div class="conteneur">
        <div class="banniere-nav">
            <div>
                <h1>Prendre un rendez-vous</h1>
                <p>Visitez nos biens — réponse sous 24h</p>
            </div>
            <a href="/gasyimmo/pages/biens.php" class="btn-retour">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Retour aux biens
            </a>
        </div>
    </div>
</div>

<section class="section">
    <div class="conteneur">

        <!-- Succès -->
        <?php if ($succes): ?>
        <div style="max-width:680px;margin:0 auto 30px;background:#dcfce7;border:1.5px solid #86efac;border-radius:14px;padding:28px;text-align:center;">
            <div style="font-size:48px;margin-bottom:12px;">✅</div>
            <h3 style="font-size:20px;font-weight:800;color:#15803d;margin-bottom:8px;">Demande envoyée avec succès !</h3>
            <p style="color:#166534;font-size:14.5px;line-height:1.7;">
                Votre demande de rendez-vous a bien été enregistrée.<br>
                Notre équipe vous contactera dans les <strong>24 heures</strong> pour confirmer la visite.
            </p>
            <div style="margin-top:18px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <a href="/gasyimmo/pages/biens.php"
                   style="padding:10px 20px;background:#1a6b3a;color:#fff;border-radius:8px;font-weight:600;font-size:13.5px;">
                    Voir d'autres biens
                </a>
                <a href="/gasyimmo/pages/rendez_vous.php"
                   style="padding:10px 20px;border:1.5px solid #86efac;color:#166534;border-radius:8px;font-weight:600;font-size:13.5px;">
                    Nouveau RDV
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Erreurs -->
        <?php if (!empty($erreurs) && !$succes): ?>
        <div class="pub-alerte pub-alerte-erreur" style="max-width:760px;margin:0 auto 20px;">
            <?php foreach ($erreurs as $err): ?><div>• <?= esc($err) ?></div><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Formulaire RDV -->
        <?php if (!$succes): ?>
        <div class="form-rdv-enveloppe">
            <h2 style="font-size:22px;font-weight:800;margin-bottom:6px;">Formulaire de demande de visite</h2>
            <p class="form-sub" style="color:#3d5c48;font-size:14px;margin-bottom:28px;">
                Remplissez ce formulaire pour demander une visite. Si vous êtes déjà client, votre CIN suffit à vous identifier.
            </p>

            <form method="POST" id="formulaire-rdv" novalidate>

                <div class="rdv-grille">
                    <!-- Prénom -->
                    <div class="rdv-groupe">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom"
                               value="<?= esc($_POST['prenom'] ?? '') ?>"
                               placeholder="Jean" required>
                        <span class="msg-erreur"></span>
                    </div>
                    <!-- Nom -->
                    <div class="rdv-groupe">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom"
                               value="<?= esc($_POST['nom'] ?? '') ?>"
                               placeholder="Rakoto" required>
                        <span class="msg-erreur"></span>
                    </div>
                    <!-- CIN -->
                    <div class="rdv-groupe">
                        <label for="cin">
                            Numéro CIN *
                            <span style="font-weight:400;text-transform:none;font-size:11px;">(12 chiffres)</span>
                        </label>
                        <input type="text" id="cin" name="cin"
                               value="<?= esc($_POST['cin'] ?? '') ?>"
                               placeholder="101234567890"
                               maxlength="12"
                               pattern="\d{12}"
                               title="Exactement 12 chiffres" required>
                        <span class="msg-erreur"></span>
                    </div>
                    <!-- Téléphone -->
                    <div class="rdv-groupe">
                        <label for="telephone">
                            Téléphone *
                            <span style="font-weight:400;text-transform:none;font-size:11px;">(10 chiffres)</span>
                        </label>
                        <input type="text" id="telephone" name="telephone"
                               value="<?= esc($_POST['telephone'] ?? '') ?>"
                               placeholder="0341234567"
                               maxlength="10"
                               pattern="\d{10}"
                               title="Exactement 10 chiffres" required>
                        <span class="msg-erreur"></span>
                    </div>
                    <!-- Email -->
                    <div class="rdv-groupe plein">
                        <label for="email">Email <span style="font-weight:400;">(facultatif)</span></label>
                        <input type="email" id="email" name="email"
                               value="<?= esc($_POST['email'] ?? '') ?>"
                               placeholder="jean.rakoto@email.mg">
                    </div>
                    <!-- Bien -->
                    <div class="rdv-groupe plein">
                        <label for="bien_id">Bien souhaité *</label>
                        <select id="bien_id" name="bien_id" required>
                            <option value="">— Sélectionner un bien —</option>
                            <?php foreach ($biensDisponibles as $b): ?>
                                <option value="<?= $b['id'] ?>"
                                    <?= (
                                        (isset($_POST['bien_id']) && (int)$_POST['bien_id']===$b['id'])
                                        || (!isset($_POST['bien_id']) && $bienPreselectionne===$b['id'])
                                    ) ? 'selected' : '' ?>>
                                    <?= esc($b['titre']) ?> — <?= esc($b['ville']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="msg-erreur"></span>
                    </div>
                    <!-- Date -->
                    <div class="rdv-groupe">
                        <label for="date_visite">Date de visite souhaitée *</label>
                        <input type="date" id="date_visite" name="date_visite"
                               value="<?= esc($_POST['date_visite'] ?? '') ?>"
                               min="<?= date('Y-m-d') ?>" required>
                        <span class="msg-erreur"></span>
                    </div>
                    <!-- Heure -->
                    <div class="rdv-groupe">
                        <label for="heure_visite">Heure souhaitée *</label>
                        <input type="time" id="heure_visite" name="heure_visite"
                               value="<?= esc($_POST['heure_visite'] ?? '') ?>"
                               min="08:00" max="17:00" required>
                        <span class="msg-erreur"></span>
                    </div>
                    <!-- Message -->
                    <div class="rdv-groupe plein">
                        <label for="message">Message <span style="font-weight:400;">(facultatif)</span></label>
                        <textarea id="message" name="message" rows="4"
                                  placeholder="Précisez vos questions, attentes ou besoins particuliers..."><?= esc($_POST['message'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" name="soumettre_rdv" class="rdv-submit">
                    Envoyer ma demande de rendez-vous
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- ═══ Suivi de rendez-vous ═══════════════════ -->
        <div class="suivi-rdv" style="max-width:760px;margin:40px auto 0;">
            <h3 style="font-size:18px;font-weight:700;margin-bottom:6px;">Suivre mes rendez-vous</h3>
            <p style="color:#3d5c48;font-size:13.5px;margin-bottom:18px;">
                Entrez votre numéro CIN pour consulter le statut de vos demandes de visite.
            </p>

            <form method="GET" style="display:flex;gap:10px;align-items:flex-end;margin-bottom:20px;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#7a9e89;display:block;margin-bottom:5px;">
                        Votre CIN (12 chiffres)
                    </label>
                    <input type="text" name="cin_suivi"
                           value="<?= esc($cinSuivi) ?>"
                           placeholder="101234567890"
                           maxlength="12"
                           style="padding:10px 13px;border:1.5px solid #dde8e2;border-radius:8px;font-size:14px;width:100%;outline:none;">
                </div>
                <button type="submit"
                        style="padding:10px 20px;background:#1a6b3a;color:#fff;border:none;border-radius:8px;font-weight:600;font-size:13.5px;cursor:pointer;white-space:nowrap;">
                    Vérifier
                </button>
            </form>

            <?php if ($cinSuivi && empty($erreurs)): ?>
                <?php if (empty($suiviRdvs)): ?>
                    <div style="text-align:center;padding:30px;color:#7a9e89;font-size:13.5px;">
                        Aucun rendez-vous trouvé pour ce CIN.
                    </div>
                <?php else: ?>
                    <?php foreach ($suiviRdvs as $sr): ?>
                    <div class="suivi-item">
                        <div class="suivi-icone" style="background:<?=
                            $sr['statut']==='accepte'    ? '#dcfce7' : (
                            $sr['statut']==='refuse'     ? '#fee2e2' : (
                            $sr['statut']==='termine'    ? '#e0f2fe' : '#fef3c7'))
                        ?>;">
                            <?= $sr['statut']==='accepte' ? '✅' : ($sr['statut']==='refuse' ? '❌' : ($sr['statut']==='termine' ? '🏁' : '⏳')) ?>
                        </div>
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:14.5px;margin-bottom:4px;"><?= esc($sr['bien_titre']) ?></div>
                            <div style="font-size:13px;color:#3d5c48;">
                                📍 <?= esc($sr['bien_ville']) ?> &nbsp;|&nbsp;
                                📅 <?= formatDate($sr['date_visite']) ?> à <?= substr($sr['heure_visite'],0,5) ?>
                            </div>
                        </div>
                        <div><?= badgeStatutRdv($sr['statut']) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/pied_public.php'; ?>
