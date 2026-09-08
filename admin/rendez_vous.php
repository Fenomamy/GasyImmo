<?php
/**
 * GasyImmo — Gestion des rendez-vous (admin)
 * Fichier : admin/rendez_vous.php
 * Accepter, refuser, marquer terminé + logique statut bien
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage     = 'Rendez-vous';
$sousTitrePage = 'Gérez les demandes de visite';

$bdd = obtenirBDD();

// ── Traitement actions POST ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rdvId  = (int)($_POST['rdv_id']  ?? 0);
    $action = $_POST['action'] ?? '';

    if ($rdvId && in_array($action, ['accepter','refuser','terminer'])) {
        $stmt = $bdd->prepare("
            SELECT r.*, b.statut AS bien_statut
            FROM rendez_vous r
            JOIN biens b ON r.bien_id = b.id
            WHERE r.id = ?
        ");
        $stmt->execute([$rdvId]);
        $rdv = $stmt->fetch();

        if ($rdv) {
            if ($action === 'accepter') {
                $bdd->prepare("UPDATE rendez_vous SET statut='accepte' WHERE id=?")->execute([$rdvId]);
                // Bien devient réservé si disponible
                if ($rdv['bien_statut'] === 'disponible') {
                    $bdd->prepare("UPDATE biens SET statut='reserve' WHERE id=?")->execute([$rdv['bien_id']]);
                }
                setFlash('succes', 'Rendez-vous accepté. Le bien est maintenant réservé.');

            } elseif ($action === 'refuser') {
                $bdd->prepare("UPDATE rendez_vous SET statut='refuse' WHERE id=?")->execute([$rdvId]);
                // Remettre disponible si c'était ce RDV qui l'avait réservé
                $bdd->prepare("UPDATE biens SET statut='disponible' WHERE id=? AND statut='reserve'")->execute([$rdv['bien_id']]);
                setFlash('succes', 'Rendez-vous refusé. Le bien redevient disponible.');

            } elseif ($action === 'terminer') {
                $bdd->prepare("UPDATE rendez_vous SET statut='termine' WHERE id=?")->execute([$rdvId]);
                setFlash('succes', 'Rendez-vous marqué comme terminé.');
            }
        }
    }

    rediriger('/gasyimmo/admin/rendez_vous.php' . ($filtreStatut ?? '' ? '?statut='.$filtreStatut : ''));
}

// ── Filtrage ─────────────────────────────────────────
$filtreStatut = $_GET['statut'] ?? '';
$params = [];
$sql = "
    SELECT r.*,
           CONCAT(c.prenom,' ',c.nom) AS client_nom,
           c.telephone  AS client_tel,
           c.email      AS client_email,
           b.titre      AS bien_titre,
           b.ville      AS bien_ville
    FROM rendez_vous r
    JOIN clients c ON r.client_id = c.id
    JOIN biens   b ON r.bien_id   = b.id
";
if ($filtreStatut) {
    $sql   .= " WHERE r.statut = ?";
    $params = [$filtreStatut];
}
$sql .= " ORDER BY r.cree_le DESC";

$stmt = $bdd->prepare($sql);
$stmt->execute($params);
$rdvs = $stmt->fetchAll();

// Comptages par statut pour les onglets
$comptages = [];
foreach (['en_attente','accepte','refuse','termine'] as $s) {
    $comptages[$s] = (int)$bdd->query("SELECT COUNT(*) FROM rendez_vous WHERE statut='$s'")->fetchColumn();
}
$comptages['tous'] = array_sum($comptages);

require_once __DIR__ . '/../includes/entete_admin.php';
?>

<!-- Onglets de filtre statut -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <?php
    $onglets = [
        ''           => ['Tous',        $comptages['tous']],
        'en_attente' => ['En attente',  $comptages['en_attente']],
        'accepte'    => ['Acceptés',    $comptages['accepte']],
        'refuse'     => ['Refusés',     $comptages['refuse']],
        'termine'    => ['Terminés',    $comptages['termine']],
    ];
    foreach ($onglets as $val => [$libelle, $nb]):
        $estActif = $filtreStatut === $val;
    ?>
    <a href="?statut=<?= esc($val) ?>"
       style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid <?= $estActif ? '#1a6b3a' : '#dde8e2' ?>;background:<?= $estActif ? '#1a6b3a' : '#fff' ?>;color:<?= $estActif ? '#fff' : '#3d5c48' ?>;text-decoration:none;">
        <?= esc($libelle) ?>
        <span style="background:<?= $estActif ? 'rgba(255,255,255,.22)' : '#f3f5f4' ?>;padding:1px 7px;border-radius:10px;font-size:11px;color:<?= $estActif ? '#fff' : '#7a9e89' ?>;">
            <?= $nb ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<!-- Table rendez-vous -->
<div class="enveloppe-table">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Bien</th>
                <th>Date visite</th>
                <th>Heure</th>
                <th>Message</th>
                <th>Statut</th>
                <th>Créé le</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rdvs)): ?>
            <tr><td colspan="9">
                <div class="etat-vide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="50" height="50">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8"  y1="2" x2="8"  y2="6"/>
                        <line x1="3"  y1="10" x2="21" y2="10"/>
                    </svg>
                    <h3>Aucun rendez-vous</h3>
                    <p>Aucun rendez-vous dans cette catégorie.</p>
                </div>
            </td></tr>
            <?php else: ?>
            <?php foreach ($rdvs as $rdv): ?>
            <tr>
                <td style="color:#7a9e89;"><?= $rdv['id'] ?></td>

                <td>
                    <div style="font-weight:600;"><?= esc($rdv['client_nom']) ?></div>
                    <div style="font-size:11.5px;color:#7a9e89;"><?= esc($rdv['client_tel']) ?></div>
                    <?php if ($rdv['client_email']): ?>
                        <div style="font-size:11px;color:#7a9e89;"><?= esc($rdv['client_email']) ?></div>
                    <?php endif; ?>
                </td>

                <td>
                    <div style="font-weight:600;max-width:160px;"><?= esc($rdv['bien_titre']) ?></div>
                    <div style="font-size:11.5px;color:#7a9e89;"><?= esc($rdv['bien_ville']) ?></div>
                </td>

                <td style="white-space:nowrap;font-weight:600;">
                    <?= formatDate($rdv['date_visite']) ?>
                </td>

                <td><?= esc(substr($rdv['heure_visite'], 0, 5)) ?></td>

                <td style="max-width:150px;">
                    <?php if ($rdv['message']): ?>
                        <span style="font-size:12.5px;color:#3d5c48;"
                              title="<?= esc($rdv['message']) ?>">
                            <?= esc(mb_substr($rdv['message'], 0, 45)) ?><?= mb_strlen($rdv['message']) > 45 ? '…' : '' ?>
                        </span>
                    <?php else: ?>
                        <span style="color:#7a9e89;">—</span>
                    <?php endif; ?>
                </td>

                <td><?= badgeStatutRdv($rdv['statut']) ?></td>

                <td style="color:#7a9e89;font-size:12px;white-space:nowrap;">
                    <?= date('d/m/Y', strtotime($rdv['cree_le'])) ?>
                </td>

                <td>
                    <div class="td-actions">
                        <?php if ($rdv['statut'] === 'en_attente'): ?>
                            <!-- Accepter -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="rdv_id" value="<?= $rdv['id'] ?>">
                                <input type="hidden" name="action" value="accepter">
                                <button type="submit" class="btn btn-primaire btn-xs"
                                        onclick="return confirm('Accepter ce rendez-vous et réserver le bien ?')">
                                    ✓ Accepter
                                </button>
                            </form>
                            <!-- Refuser -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="rdv_id" value="<?= $rdv['id'] ?>">
                                <input type="hidden" name="action" value="refuser">
                                <button type="submit" class="btn btn-danger btn-xs"
                                        onclick="return confirm('Refuser ce rendez-vous ?')">
                                    ✗ Refuser
                                </button>
                            </form>

                        <?php elseif ($rdv['statut'] === 'accepte'): ?>
                            <!-- Marquer terminé -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="rdv_id" value="<?= $rdv['id'] ?>">
                                <input type="hidden" name="action" value="terminer">
                                <button type="submit" class="btn btn-info btn-xs">
                                    ✓ Terminé
                                </button>
                            </form>
                            <!-- Créer transaction directement -->
                            <a href="/gasyimmo/admin/transaction_ajouter.php?client_id=<?= $rdv['client_id'] ?>&bien_id=<?= $rdv['bien_id'] ?>"
                               class="btn btn-warning btn-xs" title="Enregistrer une vente ou location">
                                💰 Transaction
                            </a>

                        <?php else: ?>
                            <span style="color:#7a9e89;font-size:12px;font-style:italic;">—</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/pied_admin.php'; ?>
