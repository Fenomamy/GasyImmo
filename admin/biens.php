<?php
/**
 * GasyImmo — Liste des biens (admin)
 * Fichier : admin/biens.php
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage     = 'Biens immobiliers';
$sousTitrePage = 'Gérez votre portefeuille de biens';

require_once __DIR__ . '/../includes/entete_admin.php';

$bdd = obtenirBDD();

// ── Filtres GET ──
$filtreStatut = $_GET['statut'] ?? '';
$filtreType   = $_GET['type']   ?? '';
$filtreVille  = $_GET['ville']  ?? '';
$tri          = $_GET['tri']    ?? '';

$where  = [];
$params = [];

if ($filtreStatut) { $where[] = 'statut = ?'; $params[] = $filtreStatut; }
if ($filtreType)   { $where[] = 'type = ?';   $params[] = $filtreType;   }
if ($filtreVille)  { $where[] = 'ville = ?';  $params[] = $filtreVille;  }

$sql = "SELECT * FROM biens";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);

$sql .= match($tri) {
    'prix_asc'    => ' ORDER BY prix ASC',
    'prix_desc'   => ' ORDER BY prix DESC',
    'sup_asc'     => ' ORDER BY superficie ASC',
    'sup_desc'    => ' ORDER BY superficie DESC',
    default       => ' ORDER BY date_ajout DESC',
};

$stmt  = $bdd->prepare($sql);
$stmt->execute($params);
$biens = $stmt->fetchAll();

// Villes distinctes pour filtre
$villes = $bdd->query("SELECT DISTINCT ville FROM biens ORDER BY ville")->fetchAll(PDO::FETCH_COLUMN);
?>

<!-- Barre d'outils -->
<form method="GET" class="barre-outils">
    <div class="barre-outils-gauche">
        <select name="statut" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <option value="disponible" <?= $filtreStatut==='disponible'?'selected':'' ?>>Disponible</option>
            <option value="reserve"    <?= $filtreStatut==='reserve'   ?'selected':'' ?>>Réservé</option>
            <option value="vendu"      <?= $filtreStatut==='vendu'     ?'selected':'' ?>>Vendu</option>
            <option value="loue"       <?= $filtreStatut==='loue'      ?'selected':'' ?>>Loué</option>
        </select>
        <select name="type" onchange="this.form.submit()">
            <option value="">Tous types</option>
            <option value="maison"      <?= $filtreType==='maison'      ?'selected':'' ?>>Maison</option>
            <option value="appartement" <?= $filtreType==='appartement' ?'selected':'' ?>>Appartement</option>
            <option value="terrain"     <?= $filtreType==='terrain'     ?'selected':'' ?>>Terrain</option>
        </select>
        <select name="ville" onchange="this.form.submit()">
            <option value="">Toutes villes</option>
            <?php foreach ($villes as $v): ?>
                <option value="<?= esc($v) ?>" <?= $filtreVille===$v?'selected':'' ?>><?= esc($v) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="tri" onchange="this.form.submit()">
            <option value="">Trier par défaut</option>
            <option value="prix_asc"   <?= $tri==='prix_asc'  ?'selected':'' ?>>Prix ↑</option>
            <option value="prix_desc"  <?= $tri==='prix_desc' ?'selected':'' ?>>Prix ↓</option>
            <option value="sup_asc"    <?= $tri==='sup_asc'   ?'selected':'' ?>>Superficie ↑</option>
            <option value="sup_desc"   <?= $tri==='sup_desc'  ?'selected':'' ?>>Superficie ↓</option>
        </select>
        <?php if ($filtreStatut || $filtreType || $filtreVille || $tri): ?>
            <a href="/gasyimmo/admin/biens.php" class="btn btn-secondaire btn-sm">× Réinitialiser</a>
        <?php endif; ?>
    </div>
    <div class="barre-outils-droite">
        <span style="color:#7a9e89;font-size:13px;"><?= count($biens) ?> bien(s)</span>
        <a href="/gasyimmo/admin/bien_ajouter.php" class="btn btn-primaire btn-sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nouveau bien
        </a>
    </div>
</form>

<!-- Table -->
<div class="enveloppe-table">
    <table id="table-principale">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Titre</th>
                <th>Type</th>
                <th>Ville</th>
                <th>Superficie</th>
                <th>Prix</th>
                <th>Statut</th>
                <th>Date ajout</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($biens)): ?>
            <tr><td colspan="9">
                <div class="etat-vide">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="52" height="52">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    </svg>
                    <h3>Aucun bien trouvé</h3>
                    <p>Modifiez vos filtres ou ajoutez un nouveau bien.</p>
                </div>
            </td></tr>
            <?php else: ?>
            <?php foreach ($biens as $b): ?>
            <tr>
                <td>
                    <?php if ($b['photo']): ?>
                        <img src="/gasyimmo/uploads/<?= esc($b['photo']) ?>"
                             class="vignette-bien" alt="<?= esc($b['titre']) ?>">
                    <?php else: ?>
                        <div class="vignette-vide">🏠</div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight:600;max-width:180px;"><?= esc($b['titre']) ?></div>
                    <div style="font-size:11.5px;color:#7a9e89;"><?= esc($b['adresse']) ?></div>
                </td>
                <td><?= labelType($b['type']) ?></td>
                <td><?= esc($b['ville']) ?></td>
                <td><?= $b['superficie'] ? esc($b['superficie']).' m²' : '—' ?></td>
                <td style="font-weight:700;color:#1a6b3a;white-space:nowrap;"><?= formatPrix($b['prix']) ?></td>
                <td><?= badgeStatutBien($b['statut']) ?></td>
                <td style="color:#7a9e89;font-size:12px;"><?= date('d/m/Y', strtotime($b['date_ajout'])) ?></td>
                <td>
                    <div class="td-actions">
                        <a href="/gasyimmo/admin/bien_modifier.php?id=<?= $b['id'] ?>"
                           class="btn btn-secondaire btn-xs">Modifier</a>
                        <?php if (!in_array($b['statut'], ['vendu','loue'])): ?>
                        <a href="/gasyimmo/admin/bien_supprimer.php?id=<?= $b['id'] ?>"
                           class="btn btn-danger btn-xs"
                           data-confirmer="Supprimer ce bien définitivement ?">Supprimer</a>
                        <?php else: ?>
                        <span class="btn btn-secondaire btn-xs" style="opacity:.4;cursor:not-allowed;" title="Impossible de supprimer un bien vendu ou loué">Supprimer</span>
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
