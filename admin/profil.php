<?php
/**
 * GasyImmo — Profil administrateur v2
 * Fichier : admin/profil.php
 * Modifier nom et email uniquement (téléphone/adresse fixes dans le code)
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage     = 'Mon profil';
$sousTitrePage = 'Gérer les informations du compte';

exigerAdmin();

$bdd     = obtenirBDD();
$erreurs = [];
$erreursMdp = [];

// Récupère l'admin courant
$stmt  = $bdd->prepare("SELECT * FROM administrateurs WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

if (!$admin) {
    setFlash('erreur', 'Administrateur introuvable.');
    rediriger('/gasyimmo/admin/tableau_bord.php');
}

/* ── Modifier nom + email ──────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_profil'])) {
    $nom   = trim($_POST['nom']   ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$nom)   $erreurs[] = 'Le nom est obligatoire.';
    if (!$email) $erreurs[] = 'L\'email est obligatoire.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $erreurs[] = 'Adresse email invalide.';

    if ($email && empty($erreurs)) {
        $ck = $bdd->prepare("SELECT id FROM administrateurs WHERE email = ? AND id != ?");
        $ck->execute([$email, $_SESSION['admin_id']]);
        if ($ck->fetch()) $erreurs[] = 'Cet email est déjà utilisé.';
    }

    if (empty($erreurs)) {
        $bdd->prepare("UPDATE administrateurs SET nom=?, email=? WHERE id=?")
            ->execute([$nom, $email, $_SESSION['admin_id']]);
        $_SESSION['admin_nom']   = $nom;
        $_SESSION['admin_email'] = $email;
        // Recharger
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
        setFlash('succes', 'Profil mis à jour avec succès.');
        rediriger('/gasyimmo/admin/profil.php');
    }
}

/* ── Changer le mot de passe ───────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_mdp'])) {
    $mdpActuel  = trim($_POST['mdp_actuel']    ?? '');
    $mdpNouveau = trim($_POST['mdp_nouveau']   ?? '');
    $mdpConfirm = trim($_POST['mdp_confirmer'] ?? '');

    if (!$mdpActuel)  $erreursMdp[] = 'Le mot de passe actuel est requis.';
    if (strlen($mdpNouveau) < 4) $erreursMdp[] = 'Le nouveau mot de passe doit avoir au moins 4 caractères.';
    if ($mdpNouveau !== $mdpConfirm) $erreursMdp[] = 'Les mots de passe ne correspondent pas.';

    if (empty($erreursMdp)) {
        $ck = $bdd->prepare("SELECT id FROM administrateurs WHERE id=? AND mot_passe=?");
        $ck->execute([$_SESSION['admin_id'], $mdpActuel]);
        if (!$ck->fetch()) {
            $erreursMdp[] = 'Mot de passe actuel incorrect.';
        } else {
            $bdd->prepare("UPDATE administrateurs SET mot_passe=? WHERE id=?")
                ->execute([$mdpNouveau, $_SESSION['admin_id']]);
            setFlash('succes', 'Mot de passe modifié avec succès.');
            rediriger('/gasyimmo/admin/profil.php');
        }
    }
}

require_once __DIR__ . '/../includes/entete_admin.php';
?>

<div class="grille-2" style="gap:22px;align-items:start;max-width:900px;">

    <!-- ─── INFORMATIONS DU COMPTE ──────────────────── -->
    <div class="carte">
        <div class="carte-entete">
            <span class="carte-titre">Informations du compte</span>
        </div>

        <!-- Avatar + résumé -->
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:22px;padding:16px;background:#f3f5f4;border-radius:10px;">
            <div style="width:56px;height:56px;border-radius:50%;background:#1a6b3a;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;">
                <?= strtoupper(mb_substr($admin['nom'],0,1)) ?>
            </div>
            <div>
                <div style="font-size:17px;font-weight:700;color:#0e1c12;"><?= esc($admin['nom']) ?></div>
                <div style="font-size:13px;color:#7a9e89;"><?= esc($admin['email']) ?></div>
                <div style="font-size:11.5px;color:#7a9e89;margin-top:2px;">
                    Administrateur · Depuis le <?= date('d/m/Y', strtotime($admin['cree_le'])) ?>
                </div>
            </div>
        </div>

        <?php if (!empty($erreurs)): ?>
            <div class="alerte alerte-erreur">
                <?php foreach ($erreurs as $e): ?><div>• <?= esc($e) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action_profil" value="1">
            <div class="groupe-form" style="margin-bottom:14px;">
                <label for="nom">Nom complet *</label>
                <input type="text" id="nom" name="nom"
                       value="<?= esc($admin['nom']) ?>" required
                       placeholder="Nom de l'administrateur">
            </div>
            <div class="groupe-form" style="margin-bottom:18px;">
                <label for="email">Adresse e-mail * <span style="font-weight:400;text-transform:none;">(utilisée pour la connexion)</span></label>
                <input type="email" id="email" name="email"
                       value="<?= esc($admin['email']) ?>" required
                       placeholder="admin@gasyimmo.mg">
            </div>
            <div class="actions-form">
                <button type="submit" class="btn btn-primaire">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    <!-- ─── COLONNE DROITE ──────────────────────────── -->
    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Changer mot de passe -->
        <div class="carte">
            <div class="carte-entete">
                <span class="carte-titre">Changer le mot de passe</span>
            </div>

            <?php if (!empty($erreursMdp)): ?>
                <div class="alerte alerte-erreur">
                    <?php foreach ($erreursMdp as $e): ?><div>• <?= esc($e) ?></div><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action_mdp" value="1">
                <div class="groupe-form" style="margin-bottom:12px;">
                    <label for="mdp_actuel">Mot de passe actuel *</label>
                    <input type="password" id="mdp_actuel" name="mdp_actuel" placeholder="••••••••" required>
                </div>
                <div class="groupe-form" style="margin-bottom:12px;">
                    <label for="mdp_nouveau">Nouveau mot de passe *</label>
                    <input type="password" id="mdp_nouveau" name="mdp_nouveau" placeholder="••••••••" required minlength="4">
                    <span class="indice-form">Minimum 4 caractères</span>
                </div>
                <div class="groupe-form" style="margin-bottom:16px;">
                    <label for="mdp_confirmer">Confirmer *</label>
                    <input type="password" id="mdp_confirmer" name="mdp_confirmer" placeholder="••••••••" required>
                </div>
                <div class="actions-form">
                    <button type="submit" class="btn btn-warning">
                        🔒 Changer le mot de passe
                    </button>
                </div>
            </form>
        </div>

        <!-- Infos agence (fixes) -->
        <div class="carte">
            <div class="carte-entete">
                <span class="carte-titre">Informations de l'agence</span>
            </div>
            <div style="font-size:13px;color:#3d5c48;line-height:2;">
                <div>📍 Lot 13H/BA : 3305 Beravina, Fianarantsoa</div>
                <div>🗺️ Toutes les provinces de Madagascar</div>
                <div>📞 034 00 000 00</div>
                <div>✉️ contact@gasyimmo.mg</div>
                <div>🕐 Lun–Ven : 08h–17h · Sam : 08h–12h</div>
            </div>
            <p style="font-size:11.5px;color:#7a9e89;margin-top:12px;font-style:italic;">
                Ces informations sont fixes dans l'application.
            </p>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/pied_admin.php'; ?>
