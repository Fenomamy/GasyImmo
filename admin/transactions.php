<?php
/**
 * GasyImmo — Liste des transactions (ventes & locations)
 * Fichier : admin/transactions.php
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage     = 'Ventes & Locations';
$sousTitrePage = 'Historique des transactions immobilières';

require_once __DIR__ . '/../includes/entete_admin.php';

$bdd = obtenirBDD();

// Filtres
$filtreType = $_GET['type'] ?? '';
$where  = [];
$params = [];
if ($filtreType) { $where[] = 't.type_transaction = ?'; $params[] = $filtreType; }

$sql = "
    SELECT t.*,
           CONCAT(c.prenom,' ',c.nom) AS client_nom,
           c.cin   AS client_cin,
           b.titre AS bien_titre,
           b.ville AS bien_ville,
           b.type  AS bien_type
    FROM transactions t
    JOIN clients c ON t.client_id = c.id
    JOIN biens   b ON t.bien_id   = b.id
    " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
    ORDER BY t.date_transaction DESC
";

$stmt  = $bdd->prepare($sql);
$stmt->execute($params);
$trans = $stmt->fetchAll();

// CA par type
$caVente    = (float)$bdd->query("SELECT COALESCE(SUM(montant),0) FROM transactions WHERE type_transaction='vente'")->fetchColumn();
$caLocation = (float)$bdd->query("SELECT COALESCE(SUM(montant),0) FROM transactions WHERE type_transaction='location'")->fetchColumn();
$caTotal    = $caVente + $caLocation;
?>

<!-- Résumé CA -->
<div class="grille-stats" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
    <div class="carte-stat vedette">
        <div class="stat-etiquette">Chiffre d'affaires total</div>
        <div class="stat-valeur" style="font-size:20px;"><?= formatPrix($caTotal) ?></div>
        <div class="stat-note"><?= count($trans) ?> transaction(s)</div>
    </div>
    <div class="carte-stat">
        <div class="stat-etiquette">Total Ventes</div>
        <div class="stat-valeur" style="color:#dc2626;font-size:20px;"><?= formatPrix($caVente) ?></div>
        <div class="stat-note">Biens vendus</div>
    </div>
    <div class="carte-stat">
        <div class="stat-etiquette">Total Locations</div>
        <div class="stat-valeur" style="color:#0891b2;font-size:20px;"><?= formatPrix($caLocation) ?></div>
        <div class="stat-note">Biens loués</div>
    </div>
</div>

<!-- Barre outils -->
<div class="barre-outils">
    <div class="barre-outils-gauche">
        <a href="/gasyimmo/admin/transactions.php"
           class="btn <?= !$filtreType ? 'btn-primaire' : 'btn-secondaire' ?> btn-sm">Toutes</a>
        <a href="?type=vente"
           class="btn <?= $filtreType==='vente' ? 'btn-primaire' : 'btn-secondaire' ?> btn-sm">Ventes</a>
        <a href="?type=location"
           class="btn <?= $filtreType==='location' ? 'btn-primaire' : 'btn-secondaire' ?> btn-sm">Locations</a>
    </div>
    <div class="barre-outils-droite">
        <a href="/gasyimmo/admin/transaction_ajouter.php" class="btn btn-primaire btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouvelle transaction
        </a>
    </div>
</div>

<!-- Table transactions -->
<div class="enveloppe-table">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Bien</th>
                <th>Type</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Notes</th>
                <th>Enregistré le</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($trans)): ?>
            <tr><td colspan="8">
                <div class="etat-vide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="50" height="50">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <h3>Aucune transaction enregistrée</h3>
                    <p>Ajoutez une vente ou une location.</p>
                </div>
            </td></tr>
            <?php else: ?>
            <?php foreach ($trans as $t): ?>
            <tr>
                <td style="color:#7a9e89;"><?= $t['id'] ?></td>
                <td>
                    <div style="font-weight:600;"><?= esc($t['client_nom']) ?></div>
                    <div style="font-size:11.5px;color:#7a9e89;"><?= esc($t['client_cin']) ?></div>
                </td>
                <td>
                    <div style="font-weight:600;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?= esc($t['bien_titre']) ?>
                    </div>
                    <div style="font-size:11.5px;color:#7a9e89;"><?= esc($t['bien_ville']) ?> — <?= labelType($t['bien_type']) ?></div>
                </td>
                <td>
                    <?php if ($t['type_transaction'] === 'vente'): ?>
                        <span class="badge badge-rouge">Vente</span>
                    <?php else: ?>
                        <span class="badge badge-bleu">Location</span>
                    <?php endif; ?>
                </td>
                <td style="font-weight:700;color:#1a6b3a;white-space:nowrap;">
                    <?= formatPrix($t['montant']) ?>
                </td>
                <td style="white-space:nowrap;">
                    <?= formatDate($t['date_transaction']) ?>
                </td>
                <td style="max-width:160px;font-size:12.5px;color:#3d5c48;">
                    <?= $t['notes'] ? esc(substr($t['notes'], 0, 80)) . (strlen($t['notes']) > 80 ? '…' : '') : '—' ?>
                </td>
                <td style="color:#7a9e89;font-size:12px;">
                    <?= date('d/m/Y', strtotime($t['cree_le'])) ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/pied_admin.php'; ?>
