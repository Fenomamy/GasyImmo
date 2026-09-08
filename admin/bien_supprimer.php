<?php
/**
 * GasyImmo — Supprimer un bien immobilier
 * Fichier : admin/bien_supprimer.php
 * ⚠️ Empêche la suppression si bien vendu ou loué
 */

require_once __DIR__ . '/../config/fonctions.php';

exigerAdmin();

$bdd = obtenirBDD();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) {
    rediriger('/gasyimmo/admin/biens.php');
}

$stmt = $bdd->prepare("SELECT * FROM biens WHERE id = ?");
$stmt->execute([$id]);
$bien = $stmt->fetch();

if (!$bien) {
    setFlash('erreur', 'Bien introuvable.');
    rediriger('/gasyimmo/admin/biens.php');
}

// ⚠️ Règle métier : impossible de supprimer un bien vendu ou loué
if (in_array($bien['statut'], ['vendu', 'loue'])) {
    setFlash('erreur', 'Impossible de supprimer un bien déjà vendu ou loué.');
    rediriger('/gasyimmo/admin/biens.php');
}

// Supprime la photo du disque si elle existe
if ($bien['photo']) {
    $chemin = __DIR__ . '/../uploads/' . $bien['photo'];
    if (file_exists($chemin)) {
        unlink($chemin);
    }
}

// Suppression en base (CASCADE supprime les RDV liés)
$bdd->prepare("DELETE FROM biens WHERE id = ?")->execute([$id]);

setFlash('succes', 'Le bien "' . $bien['titre'] . '" a été supprimé.');
rediriger('/gasyimmo/admin/biens.php');
