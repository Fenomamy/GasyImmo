<?php
/**
 * GasyImmo — Gestion des clients (admin)
 * Fichier : admin/clients.php
 * CRUD complet via modals JS
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage     = 'Clients';
$sousTitrePage = 'Gérez votre base de clients';

$bdd     = obtenirBDD();
$erreurs = [];

// ── Traitement POST ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter' || $action === 'modifier') {
        $nom      = trim($_POST['nom']       ?? '');
        $prenom   = trim($_POST['prenom']    ?? '');
        $cin      = trim($_POST['cin']       ?? '');
        $tel      = trim($_POST['telephone'] ?? '');
        $email    = trim($_POST['email']     ?? '');
        $adresse  = trim($_POST['adresse']   ?? '');

        // Validations
        if (!$nom)              $erreurs[] = 'Le nom est obligatoire.';
        if (!$prenom)           $erreurs[] = 'Le prénom est obligatoire.';
        if (!validerCIN($cin))  $erreurs[] = 'Le CIN doit contenir exactement 12 chiffres.';
        if (!validerTelephone($tel)) $erreurs[] = 'Le téléphone doit contenir exactement 10 chiffres.';

        if (empty($erreurs)) {
            if ($action === 'ajouter') {
                // Vérifier unicité CIN
                $ck = $bdd->prepare("SELECT id FROM clients WHERE cin = ?");
                $ck->execute([$cin]);
                if ($ck->fetch()) {
                    $erreurs[] = 'Ce numéro CIN est déjà utilisé par un autre client.';
                } else {
                    $bdd->prepare("INSERT INTO clients (nom,prenom,cin,telephone,email,adresse) VALUES (?,?,?,?,?,?)")
                        ->execute([$nom, $prenom, $cin, $tel, $email, $adresse]);
                    setFlash('succes', 'Client ajouté avec succès.');
                    rediriger('/gasyimmo/admin/clients.php');
                }
            } elseif ($action === 'modifier') {
                $cid = (int)($_POST['client_id'] ?? 0);
                // Unicité CIN sauf lui-même
                $ck = $bdd->prepare("SELECT id FROM clients WHERE cin = ? AND id != ?");
                $ck->execute([$cin, $cid]);
                if ($ck->fetch()) {
                    $erreurs[] = 'Ce CIN est déjà utilisé par un autre client.';
                } else {
                    $bdd->prepare("UPDATE clients SET nom=?,prenom=?,cin=?,telephone=?,email=?,adresse=? WHERE id=?")
                        ->execute([$nom, $prenom, $cin, $tel, $email, $adresse, $cid]);
                    setFlash('succes', 'Client modifié avec succès.');
                    rediriger('/gasyimmo/admin/clients.php');
                }
            }
        }
        // Si erreurs → afficher le modal d'ajout ouvert
    }

    if (($action ?? '') === 'supprimer') {
        $cid = (int)($_POST['client_id'] ?? 0);
        $bdd->prepare("DELETE FROM clients WHERE id = ?")->execute([$cid]);
        setFlash('succes', 'Client supprimé.');
        rediriger('/gasyimmo/admin/clients.php');
    }
}

// ── Récupération clients ─────────────────────────────
$recherche = $_GET['q'] ?? '';
$params    = [];
$sql       = "SELECT * FROM clients";
if ($recherche) {
    $sql    .= " WHERE nom LIKE ? OR prenom LIKE ? OR cin LIKE ? OR email LIKE ?";
    $params  = ["%$recherche%", "%$recherche%", "%$recherche%", "%$recherche%"];
}
$sql .= " ORDER BY cree_le DESC";

$stmt    = $bdd->prepare($sql);
$stmt->execute($params);
$clients = $stmt->fetchAll();

require_once __DIR__ . '/../includes/entete_admin.php';

// Si erreurs POST → rouvrir le modal d'ajout automatiquement
$rouvrirModal = !empty($erreurs) && (($_POST['action'] ?? '') === 'ajouter') ? 'modal-ajouter-client' : '';
?>

<?php if (!empty($erreurs)): ?>
    <div class="alerte alerte-erreur">
        <?php foreach ($erreurs as $err): ?><div>• <?= esc($err) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Barre d'outils -->
<div class="barre-outils">
    <form method="GET" class="barre-outils-gauche">
        <input type="text" name="q" id="recherche-rapide"
               placeholder="Rechercher par nom, CIN, email..."
               value="<?= esc($recherche) ?>" style="min-width:250px;">
        <button type="submit" class="btn btn-primaire btn-sm">Rechercher</button>
        <?php if ($recherche): ?>
            <a href="/gasyimmo/admin/clients.php" class="btn btn-secondaire btn-sm">× Effacer</a>
        <?php endif; ?>
    </form>
    <div class="barre-outils-droite">
        <span style="color:#7a9e89;font-size:13px;"><?= count($clients) ?> client(s)</span>
        <button class="btn btn-primaire btn-sm" data-modal-ouvrir="modal-ajouter-client">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouveau client
        </button>
    </div>
</div>

<!-- Table clients -->
<div class="enveloppe-table">
    <table id="table-principale">
        <thead>
            <tr>
                <th>#</th>
                <th>Nom complet</th>
                <th>CIN</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Adresse</th>
                <th>Inscrit le</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clients)): ?>
            <tr><td colspan="8">
                <div class="etat-vide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="50" height="50">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <h3>Aucun client trouvé</h3>
                    <p>Ajoutez un client ou modifiez la recherche.</p>
                </div>
            </td></tr>
            <?php else: ?>
            <?php foreach ($clients as $c): ?>
            <tr>
                <td style="color:#7a9e89;"><?= $c['id'] ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--vert-100,#dcfce7);display:flex;align-items:center;justify-content:center;font-weight:700;color:#1a6b3a;font-size:12px;flex-shrink:0;">
                            <?= strtoupper(mb_substr($c['prenom'],0,1)) . strtoupper(mb_substr($c['nom'],0,1)) ?>
                        </div>
                        <div>
                            <div style="font-weight:600;"><?= esc($c['prenom']) ?> <?= esc($c['nom']) ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <code style="background:#f3f5f4;padding:2px 7px;border-radius:4px;font-size:12px;">
                        <?= esc($c['cin']) ?>
                    </code>
                </td>
                <td><?= esc($c['telephone'] ?: '—') ?></td>
                <td><?= esc($c['email'] ?: '—') ?></td>
                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#3d5c48;">
                    <?= esc($c['adresse'] ?: '—') ?>
                </td>
                <td style="color:#7a9e89;font-size:12px;">
                    <?= date('d/m/Y', strtotime($c['cree_le'])) ?>
                </td>
                <td>
                    <div class="td-actions">
                        <!-- Bouton Modifier → ouvre modal avec données -->
                        <button class="btn btn-secondaire btn-xs"
                                onclick="ouvrirModalEditionClient(<?= esc(json_encode($c)) ?>)">
                            Modifier
                        </button>
                        <!-- Supprimer -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action"    value="supprimer">
                            <input type="hidden" name="client_id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-xs"
                                    onclick="return confirm('Supprimer ce client ? Ses rendez-vous seront également supprimés.')">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ═══ MODAL AJOUTER ═══════════════════════════════ -->
<div class="fond-modal <?= $rouvrirModal ? 'ouvert' : '' ?>" id="modal-ajouter-client">
    <div class="boite-modal" style="max-width:560px;">
        <div class="modal-titre">Nouveau client</div>
        <form method="POST">
            <input type="hidden" name="action" value="ajouter">
            <div class="grille-form">
                <div class="groupe-form">
                    <label>Prénom *</label>
                    <input type="text" name="prenom" placeholder="Jean" required>
                </div>
                <div class="groupe-form">
                    <label>Nom *</label>
                    <input type="text" name="nom" placeholder="Rakoto" required>
                </div>
                <div class="groupe-form">
                    <label>CIN * <span style="font-weight:400;text-transform:none;">(12 chiffres)</span></label>
                    <input type="text" name="cin" placeholder="101234567890"
                           maxlength="12" pattern="\d{12}"
                           title="Le CIN doit contenir exactement 12 chiffres" required>
                    <span class="msg-erreur"></span>
                </div>
                <div class="groupe-form">
                    <label>Téléphone * <span style="font-weight:400;text-transform:none;">(10 chiffres)</span></label>
                    <input type="text" name="telephone" placeholder="0341234567"
                           maxlength="10" pattern="\d{10}"
                           title="Le téléphone doit contenir exactement 10 chiffres" required>
                    <span class="msg-erreur"></span>
                </div>
                <div class="groupe-form plein">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="jean.rakoto@email.mg">
                </div>
                <div class="groupe-form plein">
                    <label>Adresse</label>
                    <input type="text" name="adresse" placeholder="Lot II A 47, Antananarivo">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondaire" data-modal-fermer="modal-ajouter-client">Annuler</button>
                <button type="submit" class="btn btn-primaire">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ MODAL MODIFIER ══════════════════════════════ -->
<div class="fond-modal" id="modal-edition-client">
    <div class="boite-modal" style="max-width:560px;">
        <div class="modal-titre">Modifier le client</div>
        <form method="POST">
            <input type="hidden" name="action"    value="modifier">
            <input type="hidden" name="client_id" id="edit-id">
            <div class="grille-form">
                <div class="groupe-form">
                    <label>Prénom *</label>
                    <input type="text" name="prenom" id="edit-prenom" required>
                </div>
                <div class="groupe-form">
                    <label>Nom *</label>
                    <input type="text" name="nom" id="edit-nom" required>
                </div>
                <div class="groupe-form">
                    <label>CIN * (12 chiffres)</label>
                    <input type="text" name="cin" id="edit-cin"
                           maxlength="12" pattern="\d{12}" required>
                </div>
                <div class="groupe-form">
                    <label>Téléphone * (10 chiffres)</label>
                    <input type="text" name="telephone" id="edit-telephone"
                           maxlength="10" pattern="\d{10}" required>
                </div>
                <div class="groupe-form plein">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-email">
                </div>
                <div class="groupe-form plein">
                    <label>Adresse</label>
                    <input type="text" name="adresse" id="edit-adresse">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondaire" data-modal-fermer="modal-edition-client">Annuler</button>
                <button type="submit" class="btn btn-primaire">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/pied_admin.php'; ?>
