<?php
/**
 * GasyImmo — Messages de contact (admin)
 * Fichier : admin/messages.php
 * Permet à l'admin de lire les messages du formulaire À propos
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage     = 'Messages';
$sousTitrePage = 'Messages reçus via le formulaire de contact';

$bdd = obtenirBDD();

/* ── Marquer comme lu ── */
if (isset($_GET['lire']) && (int)$_GET['lire'] > 0) {
    $bdd->prepare("UPDATE messages_contact SET lu=1 WHERE id=?")->execute([(int)$_GET['lire']]);
    rediriger('/gasyimmo/admin/messages.php');
}

/* ── Supprimer ── */
if (isset($_GET['supprimer']) && (int)$_GET['supprimer'] > 0) {
    $bdd->prepare("DELETE FROM messages_contact WHERE id=?")->execute([(int)$_GET['supprimer']]);
    setFlash('succes', 'Message supprimé.');
    rediriger('/gasyimmo/admin/messages.php');
}

/* ── Filtrage ── */
$filtre = $_GET['filtre'] ?? 'tous';
$sql = "SELECT * FROM messages_contact";
if ($filtre === 'non_lus') $sql .= " WHERE lu = 0";
$sql .= " ORDER BY cree_le DESC";

$messages  = $bdd->query($sql)->fetchAll();
$nbNonLus  = (int)$bdd->query("SELECT COUNT(*) FROM messages_contact WHERE lu=0")->fetchColumn();
$nbTotal   = (int)$bdd->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn();

require_once __DIR__ . '/../includes/entete_admin.php';
?>

<!-- Onglets -->
<div style="display:flex;gap:8px;margin-bottom:20px;">
    <a href="?filtre=tous"
       style="padding:7px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid <?= $filtre==='tous'?'#1a6b3a':'#dde8e2' ?>;background:<?= $filtre==='tous'?'#1a6b3a':'#fff' ?>;color:<?= $filtre==='tous'?'#fff':'#3d5c48' ?>;text-decoration:none;">
        Tous (<?= $nbTotal ?>)
    </a>
    <a href="?filtre=non_lus"
       style="padding:7px 16px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid <?= $filtre==='non_lus'?'#d97706':'#dde8e2' ?>;background:<?= $filtre==='non_lus'?'#d97706':'#fff' ?>;color:<?= $filtre==='non_lus'?'#fff':'#3d5c48' ?>;text-decoration:none;">
        Non lus (<?= $nbNonLus ?>)
    </a>
</div>

<?php if (empty($messages)): ?>
    <div class="etat-vide">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48">
            <path d="M4 4h16v16H4z" rx="2"/><line x1="4" y1="9" x2="20" y2="9"/>
            <line x1="9" y1="21" x2="9" y2="9"/>
        </svg>
        <h3>Aucun message</h3>
        <p>Vous n'avez reçu aucun message de contact.</p>
    </div>
<?php else: ?>

<!-- Grille de messages -->
<div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($messages as $msg): ?>
    <div style="background:#fff;border-radius:12px;border:1.5px solid <?= !$msg['lu'] ? '#fde68a' : '#dde8e2' ?>;padding:18px 22px;box-shadow:0 1px 6px rgba(0,0,0,.05);position:relative;">

        <!-- Indicateur non lu -->
        <?php if (!$msg['lu']): ?>
        <div style="position:absolute;top:18px;right:18px;width:9px;height:9px;background:#d97706;border-radius:50%;"></div>
        <?php endif; ?>

        <!-- En-tête -->
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:10px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-weight:700;color:#1a6b3a;font-size:15px;flex-shrink:0;">
                    <?= strtoupper(mb_substr($msg['nom'],0,1)) ?>
                </div>
                <div>
                    <div style="font-weight:700;font-size:14.5px;color:#0e1c12;"><?= esc($msg['nom']) ?></div>
                    <div style="font-size:12.5px;color:#7a9e89;"><?= esc($msg['email']) ?></div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <span style="font-size:12px;color:#7a9e89;"><?= date('d/m/Y à H:i', strtotime($msg['cree_le'])) ?></span>
                <?php if (!$msg['lu']): ?>
                    <span style="background:#fef3c7;color:#92400e;font-size:11px;font-weight:700;padding:2px 8px;border-radius:20px;">Non lu</span>
                <?php else: ?>
                    <span style="background:#f3f5f4;color:#7a9e89;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;">Lu</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Message -->
        <div style="background:#f8faf9;border-radius:8px;padding:13px 16px;font-size:13.5px;color:#3d5c48;line-height:1.7;margin-bottom:12px;">
            <?= nl2br(esc($msg['message'])) ?>
        </div>

        <!-- Actions -->
        <div style="display:flex;gap:8px;">
            <?php if (!$msg['lu']): ?>
                <a href="?lire=<?= $msg['id'] ?>" class="btn btn-primaire btn-xs">
                    ✓ Marquer comme lu
                </a>
            <?php endif; ?>
            <a href="mailto:<?= esc($msg['email']) ?>?subject=Re: Votre message GasyImmo"
               class="btn btn-secondaire btn-xs">
                ✉️ Répondre
            </a>
            <a href="?supprimer=<?= $msg['id'] ?>"
               class="btn btn-danger btn-xs"
               data-confirmer="Supprimer ce message définitivement ?">
                🗑️ Supprimer
            </a>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/pied_admin.php'; ?>
