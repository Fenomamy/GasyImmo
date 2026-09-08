<?php
/**
 * GasyImmo — Liste publique des biens
 * Fichier : pages/biens.php
 * Filtres : ville, statut, type | Tri : prix, superficie
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage = 'Nos biens immobiliers';

$bdd = obtenirBDD();

// ── Filtres GET ──────────────────────────────────────
$filtreVille  = $_GET['ville']  ?? '';
$filtreStatut = $_GET['statut'] ?? '';
$filtreType   = $_GET['type']   ?? '';
$tri          = $_GET['tri']    ?? '';
$prixMin      = $_GET['prix_min'] ?? '';
$prixMax      = $_GET['prix_max'] ?? '';

$where  = ["statut != 'loue'"]; // On n'affiche pas les biens déjà loués par défaut
$params = [];

// Afficher tout si demandé
if ($filtreStatut) {
    // Remplace le filtre par défaut
    array_shift($where);
    $where[] = 'statut = ?';
    $params[] = $filtreStatut;
} else {
    // Par défaut : ne pas montrer les loués et vendus
    $where = ["statut NOT IN ('vendu','loue')"];
}

if ($filtreVille) { $where[] = 'ville = ?';  $params[] = $filtreVille; }
if ($filtreType)  { $where[] = 'type = ?';   $params[] = $filtreType;  }
if ($prixMin !== '') { $where[] = 'prix >= ?'; $params[] = $prixMin; }
if ($prixMax !== '') { $where[] = 'prix <= ?'; $params[] = $prixMax; }

$sql = "SELECT * FROM biens WHERE " . implode(' AND ', $where);
$sql .= match($tri) {
    'prix_asc'   => ' ORDER BY prix ASC',
    'prix_desc'  => ' ORDER BY prix DESC',
    'sup_asc'    => ' ORDER BY superficie ASC',
    'sup_desc'   => ' ORDER BY superficie DESC',
    default      => ' ORDER BY date_ajout DESC',
};

$stmt  = $bdd->prepare($sql);
$stmt->execute($params);
$biens = $stmt->fetchAll();

// Listes pour filtres
$villes = $bdd->query("SELECT DISTINCT ville FROM biens ORDER BY ville")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/entete_public.php';
?>

<!-- Bannière page -->
<div class="banniere-page">
    <div class="conteneur">
        <div class="banniere-nav">
            <div>
                <h1>Nos biens immobiliers</h1>
                <p>Trouvez la propriété qui correspond à votre projet</p>
            </div>
            <a href="/gasyimmo/pages/accueil.php" class="btn-retour">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<!-- Contenu -->
<section class="section">
    <div class="conteneur">

        <!-- Barre de filtres -->
        <form method="GET" class="filtres-barre">
            <div class="filtre-groupe">
                <label>Type de bien</label>
                <select name="type">
                    <option value="">Tous les types</option>
                    <option value="maison"      <?= $filtreType==='maison'      ?'selected':'' ?>>Maison</option>
                    <option value="appartement" <?= $filtreType==='appartement' ?'selected':'' ?>>Appartement</option>
                    <option value="terrain"     <?= $filtreType==='terrain'     ?'selected':'' ?>>Terrain</option>
                </select>
            </div>
            <div class="filtre-groupe">
                <label>Ville</label>
                <select name="ville">
                    <option value="">Toutes les villes</option>
                    <?php foreach ($villes as $v): ?>
                        <option value="<?= esc($v) ?>" <?= $filtreVille===$v?'selected':'' ?>><?= esc($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtre-groupe">
                <label>Statut</label>
                <select name="statut">
                    <option value="">Disponibles</option>
                    <option value="disponible" <?= $filtreStatut==='disponible'?'selected':'' ?>>Disponible</option>
                    <option value="reserve"    <?= $filtreStatut==='reserve'   ?'selected':'' ?>>Réservé</option>
                </select>
            </div>
            <div class="filtre-groupe">
                <label>Prix min (Ar)</label>
                <input type="number" name="prix_min" value="<?= esc($prixMin) ?>" placeholder="0" min="0">
            </div>
            <div class="filtre-groupe">
                <label>Prix max (Ar)</label>
                <input type="number" name="prix_max" value="<?= esc($prixMax) ?>" placeholder="Illimité" min="0">
            </div>
            <div class="filtre-groupe">
                <label>Trier par</label>
                <select name="tri">
                    <option value="">Récent d'abord</option>
                    <option value="prix_asc"   <?= $tri==='prix_asc'  ?'selected':'' ?>>Prix ↑</option>
                    <option value="prix_desc"  <?= $tri==='prix_desc' ?'selected':'' ?>>Prix ↓</option>
                    <option value="sup_asc"    <?= $tri==='sup_asc'   ?'selected':'' ?>>Superficie ↑</option>
                    <option value="sup_desc"   <?= $tri==='sup_desc'  ?'selected':'' ?>>Superficie ↓</option>
                </select>
            </div>
            <button type="submit" class="filtre-btn">Rechercher</button>
            <?php if ($filtreVille||$filtreStatut||$filtreType||$tri||$prixMin||$prixMax): ?>
                <a href="/gasyimmo/pages/biens.php"
                   style="padding:10px 14px;color:#dc2626;font-size:13px;font-weight:600;border-radius:8px;background:#fef2f2;border:1.5px solid #fee2e2;align-self:flex-end;white-space:nowrap;">
                    × Effacer
                </a>
            <?php endif; ?>
        </form>

        <!-- Résultats -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <p style="color:#3d5c48;font-size:14px;">
                <strong><?= count($biens) ?></strong> bien(s) trouvé(s)
            </p>
        </div>

        <?php if (empty($biens)): ?>
            <div style="text-align:center;padding:60px 20px;color:#7a9e89;">
                <div style="font-size:56px;margin-bottom:16px;">🔍</div>
                <h3 style="font-size:18px;font-weight:700;color:#3d5c48;margin-bottom:8px;">Aucun bien trouvé</h3>
                <p>Essayez de modifier vos critères de recherche.</p>
            </div>
        <?php else: ?>
        <div class="grille-biens">
            <?php foreach ($biens as $b): ?>
            <div class="carte-bien">
                <!-- Image -->
                <?php if ($b['photo']): ?>
                    <img src="/gasyimmo/uploads/<?= esc($b['photo']) ?>"
                         class="carte-bien-img" alt="<?= esc($b['titre']) ?>">
                <?php else: ?>
                    <div class="carte-bien-img-placeholder">
                        <?= $b['type']==='terrain' ? '🌿' : ($b['type']==='appartement' ? '🏢' : '🏠') ?>
                    </div>
                <?php endif; ?>

                <div class="carte-bien-corps">
                    <div class="carte-bien-entete">
                        <h3 class="carte-bien-titre"><?= esc($b['titre']) ?></h3>
                        <span class="pub-badge pub-badge-<?= esc($b['statut']) ?>">
                            <?= ['disponible'=>'Dispo','reserve'=>'Réservé','vendu'=>'Vendu','loue'=>'Loué'][$b['statut']] ?? $b['statut'] ?>
                        </span>
                    </div>

                    <div class="carte-bien-localisation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?= esc($b['ville']) ?> — <?= labelType($b['type']) ?>
                    </div>

                    <div class="carte-bien-specs">
                        <?php if ($b['superficie']): ?>
                        <span class="spec-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                <rect x="3" y="3" width="18" height="18"/>
                            </svg>
                            <?= esc($b['superficie']) ?> m²
                        </span>
                        <?php endif; ?>
                        <?php if ($b['nb_pieces'] > 0): ?>
                        <span class="spec-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            </svg>
                            <?= $b['nb_pieces'] ?> pièce<?= $b['nb_pieces']>1?'s':'' ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="carte-bien-pied">
                        <span class="carte-bien-prix"><?= formatPrix($b['prix']) ?></span>
                        <a href="/gasyimmo/pages/bien_detail.php?id=<?= $b['id'] ?>" class="btn-voir">
                            Détails
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/pied_public.php'; ?>
