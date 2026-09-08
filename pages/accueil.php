<?php
/**
 * GasyImmo — Page d'accueil publique
 * Fichier : pages/accueil.php
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage = 'Accueil';

$bdd = obtenirBDD();

// Biens disponibles en vedette (6 derniers)
$biensVedette = $bdd->query("
    SELECT * FROM biens
    WHERE statut = 'disponible'
    ORDER BY date_ajout DESC
    LIMIT 6
")->fetchAll();

// Statistiques rapides
$stats = obtenirStatistiques();

require_once __DIR__ . '/../includes/entete_public.php';
?>

<!-- ═══ SECTION HÉRO ═══════════════════════════════ -->
<section class="hero">
    <div class="conteneur">
        <div class="hero-contenu">
            <div class="hero-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                </svg>
                Agence immobilière — Madagascar
            </div>
            <h1>Trouvez votre bien idéal à <em>Madagascar</em></h1>
            <p>
                Maisons, appartements, terrains à Antananarivo, Toamasina, Mahajanga et partout à Madagascar.
                Votre partenaire de confiance depuis plus de 10 ans.
            </p>
            <div class="hero-btns">
                <a href="/gasyimmo/pages/biens.php" class="hero-btn-principal">
                    Voir tous les biens
                </a>
                <a href="/gasyimmo/pages/rendez_vous.php" class="hero-btn-secondaire">
                    Prendre un RDV
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══ STATISTIQUES RAPIDES ══════════════════════ -->
<section class="stats-rapides">
    <div class="conteneur">
        <div class="stats-rapides-grille">
            <div class="qs-item">
                <span class="qs-chiffre" data-compter="<?= $stats['total_biens'] ?>"><?= $stats['total_biens'] ?></span>
                <span class="qs-libelle">Biens disponibles</span>
            </div>
            <div class="qs-item">
                <span class="qs-chiffre" data-compter="<?= $stats['total_clients'] ?>"><?= $stats['total_clients'] ?></span>
                <span class="qs-libelle">Clients satisfaits</span>
            </div>
            <div class="qs-item">
                <span class="qs-chiffre" data-compter="<?= $stats['vendus'] ?>"><?= $stats['vendus'] ?></span>
                <span class="qs-libelle">Biens vendus</span>
            </div>
            <div class="qs-item">
                <span class="qs-chiffre" data-compter="10">10</span>
                <span class="qs-libelle">Ans d'expérience</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══ BIENS EN VEDETTE ═══════════════════════════ -->
<section class="section section-alt">
    <div class="conteneur">
        <div class="section-entete">
            <span class="section-label">Nos biens</span>
            <h2 class="section-titre">Biens immobiliers disponibles</h2>
            <p class="section-sous-titre">
                Découvrez notre sélection de biens à vendre et à louer à travers Madagascar.
            </p>
        </div>

        <?php if (empty($biensVedette)): ?>
            <p style="text-align:center;color:#7a9e89;">Aucun bien disponible pour le moment.</p>
        <?php else: ?>
        <div class="grille-biens">
            <?php foreach ($biensVedette as $b): ?>
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
                        <span class="pub-badge pub-badge-<?= esc($b['type']) ?>"><?= labelType($b['type']) ?></span>
                    </div>

                    <div class="carte-bien-localisation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <?= esc($b['ville']) ?>
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
                        <?php if ($b['etage'] !== null): ?>
                        <span class="spec-item">Étage <?= $b['etage'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="carte-bien-pied">
                        <span class="carte-bien-prix"><?= formatPrix($b['prix']) ?></span>
                        <a href="/gasyimmo/pages/bien_detail.php?id=<?= $b['id'] ?>" class="btn-voir">
                            Voir
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

        <div style="text-align:center;margin-top:40px;">
            <a href="/gasyimmo/pages/biens.php" class="hero-btn-principal" style="display:inline-flex;align-items:center;gap:8px;">
                Voir tous les biens disponibles
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- ═══ POURQUOI NOUS CHOISIR ══════════════════════ -->
<section class="section">
    <div class="conteneur">
        <div class="section-entete">
            <span class="section-label">Nos avantages</span>
            <h2 class="section-titre">Pourquoi choisir GasyImmo ?</h2>
        </div>

        <div class="grille-biens" style="grid-template-columns:repeat(3,1fr);">
            <div style="background:#fff;border-radius:14px;padding:28px;border:1px solid #dde8e2;text-align:center;">
                <div style="width:56px;height:56px;background:#f0fdf4;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;">🏆</div>
                <h3 style="font-size:16px;font-weight:700;margin-bottom:8px;">Expert local</h3>
                <p style="font-size:13.5px;color:#3d5c48;line-height:1.7;">10 ans d'expérience dans l'immobilier malgache. Nous connaissons chaque ville, chaque quartier.</p>
            </div>
            <div style="background:#fff;border-radius:14px;padding:28px;border:1px solid #dde8e2;text-align:center;">
                <div style="width:56px;height:56px;background:#f0fdf4;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;">🤝</div>
                <h3 style="font-size:16px;font-weight:700;margin-bottom:8px;">Accompagnement personnalisé</h3>
                <p style="font-size:13.5px;color:#3d5c48;line-height:1.7;">Un conseiller dédié vous accompagne de la recherche jusqu'à la signature finale.</p>
            </div>
            <div style="background:#fff;border-radius:14px;padding:28px;border:1px solid #dde8e2;text-align:center;">
                <div style="width:56px;height:56px;background:#f0fdf4;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:26px;">🔒</div>
                <h3 style="font-size:16px;font-weight:700;margin-bottom:8px;">Transactions sécurisées</h3>
                <p style="font-size:13.5px;color:#3d5c48;line-height:1.7;">Tous nos biens sont vérifiés. Actes notariés, baux conformes à la législation malgache.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══ CTA RENDEZ-VOUS ═══════════════════════════ -->
<section style="background:linear-gradient(135deg,#0a2116,#1a4a2a);padding:60px 0;color:#fff;">
    <div class="conteneur" style="text-align:center;">
        <h2 style="font-size:30px;font-weight:800;margin-bottom:12px;">Vous avez trouvé un bien qui vous intéresse ?</h2>
        <p style="color:rgba(255,255,255,.68);font-size:15px;margin-bottom:28px;">
            Demandez une visite gratuitement — notre équipe vous contacte sous 24h.
        </p>
        <a href="/gasyimmo/pages/rendez_vous.php" class="hero-btn-principal">
            Prendre un rendez-vous →
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/pied_public.php'; ?>
