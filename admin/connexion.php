<?php
/**
 * GasyImmo — Page de connexion administrateur
 * Fichier : admin/connexion.php
 */

require_once __DIR__ . '/../config/fonctions.php';

demarrerSession();

// Si déjà connecté → tableau de bord
if (!empty($_SESSION['admin_id'])) {
    rediriger('/gasyimmo/admin/tableau_bord.php');
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $mot_passe   = trim($_POST['mot_passe']   ?? '');

    if ($identifiant === '' || $mot_passe === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $bdd   = obtenirBDD();
        $stmt  = $bdd->prepare("SELECT * FROM administrateurs WHERE identifiant = ? AND mot_passe = ?");
        $stmt->execute([$identifiant, $mot_passe]);
        $admin = $stmt->fetch();

        if ($admin) {
            $_SESSION['admin_id']  = $admin['id'];
            $_SESSION['admin_nom'] = $admin['nom'];
            setFlash('succes', 'Bienvenue ' . $admin['nom'] . ' !');
            rediriger('/gasyimmo/admin/tableau_bord.php');
        } else {
            $erreur = 'Identifiant ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin — GasyImmo</title>
    <link rel="stylesheet" href="/gasyimmo/assets/css/admin.css">
</head>
<body class="page-connexion">

<div class="carte-connexion">

    <!-- Logo -->
    <div class="connexion-logo">
        <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="36" height="36" rx="10" fill="#1a6b3a"/>
            <path d="M18 7L29 15V31H23V24H13V31H7V15L18 7Z" fill="white"/>
            <circle cx="18" cy="18" r="2.5" fill="#4ade80"/>
        </svg>
        <span class="connexion-logo-texte">GasyImmo</span>
    </div>
    <p class="connexion-sous-titre">Espace Administrateur — Connexion sécurisée</p>

    <!-- Erreur -->
    <?php if ($erreur): ?>
        <div class="alerte alerte-erreur"><?= esc($erreur) ?></div>
    <?php endif; ?>

    <!-- Formulaire -->
    <form method="POST" class="connexion-form" id="form-connexion">
        <div class="groupe-form">
            <label for="identifiant">Identifiant</label>
            <input type="text" id="identifiant" name="identifiant"
                   value="<?= esc($_POST['identifiant'] ?? '') ?>"
                   placeholder="admin" required autofocus>
        </div>
        <div class="groupe-form">
            <label for="mot_passe">Mot de passe</label>
            <input type="password" id="mot_passe" name="mot_passe"
                   placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primaire btn-full" style="margin-top:6px;">
            Se connecter
        </button>
    </form>

    <!-- Liens bas de page -->
    <div class="connexion-liens">
        <a href="/gasyimmo/pages/accueil.php">← Retour au site</a>
        <span style="color:#dde8e2;">|</span>
        <span>admin / admin123</span>
    </div>

</div>

<script src="/gasyimmo/assets/js/admin.js"></script>
</body>
</html>
