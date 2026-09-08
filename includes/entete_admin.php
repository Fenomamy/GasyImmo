<?php
/**
 * GasyImmo — En-tête admin avec sidebar
 * Fichier : includes/entete_admin.php
 * Appelé après avoir défini $titrePage et exigerAdmin()
 */

exigerAdmin();

// Déterminer la page active pour le menu
$pageCourante = basename($_SERVER['PHP_SELF'], '.php');

// Nombre de RDV en attente (badge sidebar)
$bdd      = obtenirBDD();
$nbAttente = (int)$bdd->query("SELECT COUNT(*) FROM rendez_vous WHERE statut='en_attente'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= esc($titrePage ?? 'Admin') ?> — GasyImmo</title>
    <link rel="stylesheet" href="/gasyimmo/assets/css/admin.css">
</head>
<body>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<aside class="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="/gasyimmo/admin/tableau_bord.php" class="logo-lien">
            <div class="logo-icone">
                <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="36" height="36" rx="10" fill="#1a6b3a"/>
                    <path d="M18 7L29 15V31H23V24H13V31H7V15L18 7Z" fill="white"/>
                    <circle cx="18" cy="18" r="2.5" fill="#4ade80"/>
                </svg>
            </div>
            <span class="logo-texte">GasyImmo</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <p class="nav-label">MENU PRINCIPAL</p>

        <a href="/gasyimmo/admin/tableau_bord.php"
           class="nav-lien <?= $pageCourante === 'tableau_bord' ? 'actif' : '' ?>">
            <svg class="nav-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
            </svg>
            <span>Tableau de bord</span>
        </a>

        <a href="/gasyimmo/admin/biens.php"
           class="nav-lien <?= in_array($pageCourante, ['biens','bien_ajouter','bien_modifier']) ? 'actif' : '' ?>">
            <svg class="nav-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Biens immobiliers</span>
        </a>

        <a href="/gasyimmo/admin/clients.php"
           class="nav-lien <?= in_array($pageCourante, ['clients']) ? 'actif' : '' ?>">
            <svg class="nav-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>Clients</span>
        </a>

        <a href="/gasyimmo/admin/rendez_vous.php"
           class="nav-lien <?= $pageCourante === 'rendez_vous' ? 'actif' : '' ?>">
            <svg class="nav-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span>Rendez-vous</span>
            <?php if ($nbAttente > 0): ?>
                <span class="nav-badge"><?= $nbAttente ?></span>
            <?php endif; ?>
        </a>

        <a href="/gasyimmo/admin/transactions.php"
           class="nav-lien <?= in_array($pageCourante, ['transactions','transaction_ajouter']) ? 'actif' : '' ?>">
            <svg class="nav-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            <span>Ventes &amp; Locations</span>
        </a>

        <p class="nav-label" style="margin-top:16px;">ACCÈS RAPIDE</p>

        <a href="/gasyimmo/pages/accueil.php" target="_blank" class="nav-lien">
            <svg class="nav-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10z"/>
            </svg>
            <span>Site public</span>
        </a>

        <a href="/gasyimmo/admin/deconnexion.php" class="nav-lien nav-deconnexion">
            <svg class="nav-icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Déconnexion</span>
        </a>

    </nav>

    <!-- Profil admin en bas -->
    <div class="sidebar-profil">
        <div class="profil-avatar">
            <?= strtoupper(mb_substr($_SESSION['admin_nom'] ?? 'A', 0, 1)) ?>
        </div>
        <div class="profil-info">
            <div class="profil-nom"><?= esc($_SESSION['admin_nom'] ?? 'Administrateur') ?></div>
            <div class="profil-role">Administrateur</div>
        </div>
    </div>

</aside>

<!-- ═══════════════ CONTENU PRINCIPAL ═══════════════ -->
<main class="contenu-principal">

    <!-- Barre supérieure -->
    <header class="barre-superieure">
        <div class="bs-gauche">
            <h1 class="titre-page"><?= esc($titrePage ?? 'Dashboard') ?></h1>
            <?php if (!empty($sousTitrePage)): ?>
                <p class="sous-titre-page"><?= esc($sousTitrePage) ?></p>
            <?php endif; ?>
        </div>
        <div class="bs-droite">
            <a href="/gasyimmo/admin/bien_ajouter.php" class="btn btn-primaire btn-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Ajouter un bien
            </a>
        </div>
    </header>

    <!-- Messages flash -->
    <?php afficherFlash(); ?>

    <!-- Zone de contenu -->
    <div class="zone-contenu">
