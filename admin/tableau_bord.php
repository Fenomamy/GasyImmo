<?php
/**
 * GasyImmo — Tableau de bord administrateur
 * Fichier : admin/tableau_bord.php
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage     = 'Tableau de bord';
$sousTitrePage = 'Vue d\'ensemble de l\'activité GasyImmo';

require_once __DIR__ . '/../includes/entete_admin.php';

$bdd   = obtenirBDD();
$stats = obtenirStatistiques();

// Derniers rendez-vous
$rdvRecents = $bdd->query("
    SELECT r.*, CONCAT(c.prenom,' ',c.nom) AS client_nom, b.titre AS bien_titre
    FROM rendez_vous r
    JOIN clients c ON r.client_id = c.id
    JOIN biens   b ON r.bien_id   = b.id
    ORDER BY r.cree_le DESC LIMIT 5
")->fetchAll();

// Dernières transactions
$transRecentes = $bdd->query("
    SELECT t.*, CONCAT(c.prenom,' ',c.nom) AS client_nom, b.titre AS bien_titre
    FROM transactions t
    JOIN clients c ON t.client_id = c.id
    JOIN biens   b ON t.bien_id   = b.id
    ORDER BY t.cree_le DESC LIMIT 4
")->fetchAll();

// Données graphiques
$parType  = $stats['par_type'];
$parVille = $stats['par_ville'];

$typeLabels   = implode(',', array_keys($parType));
$typeValeurs  = implode(',', array_values($parType));
$typeCouleurs = '#1a6b3a,#2d9a55,#4ade80';

$statutValeurs = $stats['disponibles'].','. $stats['reserves'].','. $stats['vendus'].','. $stats['loues'];
$statutLabels  = 'Disponible,Réservé,Vendu,Loué';
$statutCouleurs= '#16a34a,#d97706,#dc2626,#0891b2';

$villeLabels  = implode(',', array_column($parVille, 'ville'));
$villeValeurs = implode(',', array_column($parVille, 'nb'));
$villeCouleurs= '#1a6b3a,#2d9a55,#3db568,#4ade80,#86efac,#bbf7d0,#dcfce7,#f0fdf4';
?>

<!-- ─── CARTES STATISTIQUES ────────────────────────── -->
<div class="grille-stats">

    <div class="carte-stat vedette">
        <div class="stat-icone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            </svg>
        </div>
        <div class="stat-etiquette">Total biens</div>
        <div class="stat-valeur"><?= $stats['total_biens'] ?></div>
        <div class="stat-note">Biens enregistrés</div>
    </div>

    <div class="carte-stat">
        <div class="stat-icone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
        </div>
        <div class="stat-etiquette">Clients</div>
        <div class="stat-valeur"><?= $stats['total_clients'] ?></div>
        <div class="stat-note">Clients enregistrés</div>
    </div>

    <div class="carte-stat">
        <div class="stat-icone" style="background:#fef3c7;color:#d97706;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="stat-etiquette">RDV en attente</div>
        <div class="stat-valeur" style="color:#d97706;"><?= $stats['rdv_attente'] ?></div>
        <div class="stat-note">À traiter</div>
    </div>

    <div class="carte-stat">
        <div class="stat-icone" style="background:#dcfce7;color:#16a34a;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="stat-etiquette">Chiffre d'affaires</div>
        <div class="stat-valeur" style="font-size:18px;color:#16a34a;"><?= formatPrix($stats['ca_total']) ?></div>
        <div class="stat-note">Total transactions</div>
    </div>

</div>

<!-- ─── GRAPHIQUES ────────────────────────────────── -->
<div class="grille-graphiques">

    <!-- Donut Types -->
    <div class="carte">
        <div class="carte-entete">
            <span class="carte-titre">Répartition par type</span>
        </div>
        <div data-graphique-donut
             data-segments="<?= $typeValeurs ?>"
             data-couleurs="<?= $typeCouleurs ?>"
             data-etiquettes="<?= $typeLabels ?>"
             data-etiquette-centre="Biens">
        </div>
    </div>

    <!-- Donut Statuts -->
    <div class="carte">
        <div class="carte-entete">
            <span class="carte-titre">Statuts des biens</span>
        </div>
        <div data-graphique-donut
             data-segments="<?= $statutValeurs ?>"
             data-couleurs="<?= $statutCouleurs ?>"
             data-etiquettes="<?= $statutLabels ?>"
             data-etiquette-centre="Biens">
        </div>
    </div>

    <!-- Barres Villes -->
    <div class="carte">
        <div class="carte-entete">
            <span class="carte-titre">Biens par ville</span>
        </div>
        <div data-graphique-barres
             data-valeurs="<?= $villeValeurs ?>"
             data-etiquettes="<?= $villeLabels ?>"
             data-couleurs="<?= $villeCouleurs ?>">
        </div>
    </div>

</div>

<!-- ─── TABLES RÉCENTES ───────────────────────────── -->
<div class="grille-2" style="margin-top:24px;">

    <!-- Derniers RDV -->
    <div class="carte">
        <div class="carte-entete">
            <span class="carte-titre">Derniers rendez-vous</span>
            <a href="/gasyimmo/admin/rendez_vous.php" class="btn btn-secondaire btn-sm">Voir tout</a>
        </div>
        <?php if (empty($rdvRecents)): ?>
            <p style="color:#7a9e89;font-size:13px;">Aucun rendez-vous.</p>
        <?php else: ?>
        <div class="enveloppe-table">
            <table>
                <thead><tr>
                    <th>Client</th><th>Bien</th><th>Date</th><th>Statut</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($rdvRecents as $rdv): ?>
                    <tr>
                        <td style="font-weight:600;"><?= esc($rdv['client_nom']) ?></td>
                        <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= esc($rdv['bien_titre']) ?></td>
                        <td><?= esc($rdv['date_visite']) ?></td>
                        <td><?= badgeStatutRdv($rdv['statut']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Dernières transactions -->
    <div class="carte">
        <div class="carte-entete">
            <span class="carte-titre">Dernières transactions</span>
            <a href="/gasyimmo/admin/transactions.php" class="btn btn-secondaire btn-sm">Voir tout</a>
        </div>
        <?php if (empty($transRecentes)): ?>
            <p style="color:#7a9e89;font-size:13px;">Aucune transaction.</p>
        <?php else: ?>
        <div class="enveloppe-table">
            <table>
                <thead><tr>
                    <th>Client</th><th>Bien</th><th>Type</th><th>Montant</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($transRecentes as $t): ?>
                    <tr>
                        <td style="font-weight:600;"><?= esc($t['client_nom']) ?></td>
                        <td style="max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= esc($t['bien_titre']) ?></td>
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/pied_admin.php'; ?>
