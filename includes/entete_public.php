<?php
/**
 * GasyImmo — En-tête site public
 * Fichier : includes/entete_public.php
 */

demarrerSession();
$pagePublique = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titrePage ?? 'GasyImmo') ?> — Immobilier à Madagascar</title>
    <link rel="stylesheet" href="/gasyimmo/assets/css/public.css">
</head>
<body>

<!-- Navigation publique -->
<header class="nav-public">
    <div class="nav-conteneur">
        <!-- Logo cliquable → accueil admin discret -->
        <a href="/gasyimmo/pages/accueil.php" class="nav-logo">
            <div class="nav-logo-icone">
                <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="36" height="36" rx="10" fill="#1a6b3a"/>
                    <path d="M18 7L29 15V31H23V24H13V31H7V15L18 7Z" fill="white"/>
                    <circle cx="18" cy="18" r="2.5" fill="#4ade80"/>
                </svg>
            </div>
            <span class="nav-logo-texte">GasyImmo</span>
        </a>

        <!-- Liens de navigation -->
        <nav class="nav-liens">
            <a href="/gasyimmo/pages/accueil.php"
               class="nav-lien <?= $pagePublique === 'accueil' ? 'actif' : '' ?>">Accueil</a>
            <a href="/gasyimmo/pages/biens.php"
               class="nav-lien <?= $pagePublique === 'biens' ? 'actif' : '' ?>">Nos biens</a>
            <a href="/gasyimmo/pages/rendez_vous.php"
               class="nav-lien <?= $pagePublique === 'rendez_vous' ? 'actif' : '' ?>">Rendez-vous</a>
            <a href="/gasyimmo/pages/a_propos.php"
               class="nav-lien <?= $pagePublique === 'a_propos' ? 'actif' : '' ?>">À propos</a>
        </nav>

        <!-- Accès admin discret via lien texte -->
        <a href="/gasyimmo/admin/connexion.php" class="nav-btn-admin" title="Espace administration">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                <circle cx="12" cy="8" r="4"/>
                <path d="M6 20v-2a6 6 0 0 1 12 0v2"/>
            </svg>
            Admin
        </a>
    </div>
</header>

<!-- Message flash public -->
<?php afficherFlash(); ?>
