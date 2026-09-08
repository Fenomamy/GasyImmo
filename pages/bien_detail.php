<?php
/**
 * GasyImmo — Détail d'un bien (public)
 * Fichier : pages/bien_detail.php
 */

require_once __DIR__ . '/../config/fonctions.php';

$bdd = obtenirBDD();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) rediriger('/gasyimmo/pages/biens.php');

$stmt = $bdd->prepare("SELECT * FROM biens WHERE id = ?");
$stmt->execute([$id]);
$bien = $stmt->fetch();

if (!$bien) {
    setFlash('erreur', 'Ce bien n\'existe pas ou a été supprimé.');
    rediriger('/gasyimmo/pages/biens.php');
}

$titrePage = $bien['titre'];

require_once __DIR__ . '/../includes/entete_public.php';
?>

<!-- Bannière -->
<div class="banniere-page">
    <div class="conteneur">
        <div class="banniere-nav">
            <div>
                <h1 style="font-size:26px;"><?= esc($bien['titre']) ?></h1>
                <p><?= esc($bien['ville']) ?> — <?= labelType($bien['type']) ?></p>
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

<!-- Contenu détail -->
<section class="section">
    <div class="conteneur">
        <div class="bien-detail-disposition">

            <!-- Colonne gauche : image + description -->
            <div>
                <!-- Image -->
                <?php if ($bien['photo']): ?>
                    <img src="/gasyimmo/uploads/<?= esc($bien['photo']) ?>"
                         class="bien-detail-img" alt="<?= esc($bien['titre']) ?>">
                <?php else: ?>
                    <div class="bien-detail-img-placeholder">
                        <?= $bien['type']==='terrain' ? '🌿' : ($bien['type']==='appartement' ? '🏢' : '🏠') ?>
                    </div>
                <?php endif; ?>

                <!-- Description -->
                <?php if ($bien['description']): ?>
                <div style="background:#fff;border-radius:14px;padding:24px;border:1px solid #dde8e2;margin-top:20px;box-shadow:0 4px 20px rgba(0,0,0,.08);">
                    <h3 style="font-size:17px;font-weight:700;margin-bottom:12px;color:#0e1c12;">Description</h3>
                    <p style="color:#3d5c48;line-height:1.8;font-size:14.5px;"><?= nl2br(esc($bien['description'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Caractéristiques détaillées -->
                <div style="background:#fff;border-radius:14px;padding:24px;border:1px solid #dde8e2;margin-top:20px;box-shadow:0 4px 20px rgba(0,0,0,.08);">
                    <h3 style="font-size:17px;font-weight:700;margin-bottom:16px;color:#0e1c12;">Caractéristiques</h3>

                    <div class="ligne-detail" style="display:flex;padding:10px 0;border-bottom:1px solid #dde8e2;font-size:13.5px;">
                        <span style="width:160px;font-weight:600;color:#3d5c48;flex-shrink:0;">Type</span>
                        <span><?= labelType($bien['type']) ?></span>
                    </div>
                    <div class="ligne-detail" style="display:flex;padding:10px 0;border-bottom:1px solid #dde8e2;font-size:13.5px;">
                        <span style="width:160px;font-weight:600;color:#3d5c48;flex-shrink:0;">Ville</span>
                        <span><?= esc($bien['ville']) ?></span>
                    </div>
                    <?php if ($bien['adresse']): ?>
                    <div class="ligne-detail" style="display:flex;padding:10px 0;border-bottom:1px solid #dde8e2;font-size:13.5px;">
                        <span style="width:160px;font-weight:600;color:#3d5c48;flex-shrink:0;">Adresse</span>
                        <span><?= esc($bien['adresse']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($bien['superficie']): ?>
                    <div class="ligne-detail" style="display:flex;padding:10px 0;border-bottom:1px solid #dde8e2;font-size:13.5px;">
                        <span style="width:160px;font-weight:600;color:#3d5c48;flex-shrink:0;">Superficie</span>
                        <span><?= esc($bien['superficie']) ?> m²</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($bien['nb_pieces'] > 0): ?>
                    <div class="ligne-detail" style="display:flex;padding:10px 0;border-bottom:1px solid #dde8e2;font-size:13.5px;">
                        <span style="width:160px;font-weight:600;color:#3d5c48;flex-shrink:0;">Pièces</span>
                        <span><?= $bien['nb_pieces'] ?> pièce<?= $bien['nb_pieces']>1?'s':'' ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($bien['etage'] !== null): ?>
                    <div class="ligne-detail" style="display:flex;padding:10px 0;border-bottom:1px solid #dde8e2;font-size:13.5px;">
                        <span style="width:160px;font-weight:600;color:#3d5c48;flex-shrink:0;">Étage</span>
                        <span><?= $bien['etage'] ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="ligne-detail" style="display:flex;padding:10px 0;font-size:13.5px;">
                        <span style="width:160px;font-weight:600;color:#3d5c48;flex-shrink:0;">Ajouté le</span>
                        <span><?= formatDate($bien['date_ajout']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : infos prix + actions -->
            <div class="bien-detail-infos">
                <!-- Statut -->
                <div style="margin-bottom:14px;">
                    <?= badgeStatutBien($bien['statut']) ?>
                    <span class="pub-badge pub-badge-<?= esc($bien['type']) ?>" style="margin-left:6px;">
                        <?= labelType($bien['type']) ?>
                    </span>
                </div>

                <!-- Prix -->
                <div style="font-size:28px;font-weight:800;color:#1a6b3a;margin-bottom:6px;">
                    <?= formatPrix($bien['prix']) ?>
                </div>
                <div style="font-size:13px;color:#7a9e89;margin-bottom:20px;">Prix de vente / location</div>

                <!-- Specs rapides -->
                <div class="bien-specs-grille">
                    <?php if ($bien['superficie']): ?>
                    <div class="spec-carte">
                        <div class="spec-carte-val"><?= esc($bien['superficie']) ?></div>
                        <div class="spec-carte-libelle">m²</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($bien['nb_pieces'] > 0): ?>
                    <div class="spec-carte">
                        <div class="spec-carte-val"><?= $bien['nb_pieces'] ?></div>
                        <div class="spec-carte-libelle">Pièces</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($bien['etage'] !== null): ?>
                    <div class="spec-carte">
                        <div class="spec-carte-val"><?= $bien['etage'] ?></div>
                        <div class="spec-carte-libelle">Étage</div>
                    </div>
                    <?php endif; ?>
                    <div class="spec-carte">
                        <div class="spec-carte-val" style="font-size:14px;"><?= esc($bien['ville']) ?></div>
                        <div class="spec-carte-libelle">Ville</div>
                    </div>
                </div>

                <!-- CTA -->
                <?php if ($bien['statut'] === 'disponible'): ?>
                <a href="/gasyimmo/pages/rendez_vous.php?bien_id=<?= $bien['id'] ?>"
                   style="display:block;width:100%;padding:13px;background:#1a6b3a;color:#fff;border-radius:8px;text-align:center;font-weight:700;font-size:14.5px;margin-top:20px;transition:background .15s;">
                    📅 Demander une visite
                </a>
                <a href="/gasyimmo/pages/a_propos.php"
                   style="display:block;width:100%;padding:11px;border:1.5px solid #dde8e2;color:#3d5c48;border-radius:8px;text-align:center;font-weight:600;font-size:13.5px;margin-top:10px;">
                    📞 Nous contacter
                </a>
                <?php elseif ($bien['statut'] === 'reserve'): ?>
                <div style="background:#fef3c7;border:1.5px solid #fde68a;border-radius:8px;padding:14px;margin-top:20px;text-align:center;color:#92400e;font-size:13.5px;font-weight:600;">
                    ⏳ Ce bien est actuellement réservé.<br>
                    <span style="font-weight:400;">Contactez-nous pour être sur liste d'attente.</span>
                </div>
                <?php else: ?>
                <div style="background:#fee2e2;border:1.5px solid #fecaca;border-radius:8px;padding:14px;margin-top:20px;text-align:center;color:#991b1b;font-size:13.5px;font-weight:600;">
                    Ce bien n'est plus disponible.
                </div>
                <?php endif; ?>

                <!-- Contact agence -->
                <div style="border-top:1px solid #dde8e2;margin-top:22px;padding-top:18px;">
                    <p style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#7a9e89;margin-bottom:12px;">Contact agence</p>
                    <p style="font-size:13.5px;color:#3d5c48;margin-bottom:6px;">📍 Analakely, Antananarivo</p>
                    <p style="font-size:13.5px;color:#3d5c48;margin-bottom:6px;">📞 034 00 000 00</p>
                    <p style="font-size:13.5px;color:#3d5c48;">✉️ contact@gasyimmo.mg</p>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/pied_public.php'; ?>
