<?php
/**
 * GasyImmo — Fonctions utilitaires globales
 * Fichier : config/fonctions.php
 */

require_once __DIR__ . '/connexion.php';

// ================================================================
//  SÉCURITÉ & SESSION
// ================================================================

/** Démarre la session si non démarrée */
function demarrerSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/** Protège une page admin — redirige si non connecté */
function exigerAdmin(): void {
    demarrerSession();
    if (empty($_SESSION['admin_id'])) {
        header('Location: /gasyimmo/admin/connexion.php');
        exit;
    }
}

/** Redirige vers une URL */
function rediriger(string $url): void {
    header('Location: ' . $url);
    exit;
}

/** Échappe une chaîne pour affichage HTML sécurisé */
function esc(mixed $valeur): string {
    return htmlspecialchars((string)$valeur, ENT_QUOTES, 'UTF-8');
}

// ================================================================
//  VALIDATION LOCALE (Madagascar)
// ================================================================

/**
 * Valide un CIN malgache : exactement 12 chiffres
 */
function validerCIN(string $cin): bool {
    return (bool) preg_match('/^\d{12}$/', trim($cin));
}

/**
 * Valide un numéro de téléphone malgache : exactement 10 chiffres
 */
function validerTelephone(string $tel): bool {
    return (bool) preg_match('/^\d{10}$/', trim($tel));
}

// ================================================================
//  FORMATAGE
// ================================================================

/** Formate un montant en Ariary malgache */
function formatPrix(float|int $montant): string {
    return number_format((float)$montant, 0, ',', ' ') . ' Ar';
}

/** Formate une date au format français */
function formatDate(string $date): string {
    if (!$date) return '—';
    $ts    = strtotime($date);
    $mois  = ['','Janvier','Février','Mars','Avril','Mai','Juin',
               'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    return date('d', $ts) . ' ' . $mois[(int)date('m', $ts)] . ' ' . date('Y', $ts);
}

/** Badge HTML coloré pour le statut d'un bien */
function badgeStatutBien(string $statut): string {
    $map = [
        'disponible' => ['Disponible', 'badge-vert'],
        'reserve'    => ['Réservé',    'badge-orange'],
        'vendu'      => ['Vendu',      'badge-rouge'],
        'loue'       => ['Loué',       'badge-bleu'],
    ];
    $b = $map[$statut] ?? ['Inconnu', 'badge-gris'];
    return '<span class="badge ' . $b[1] . '">' . $b[0] . '</span>';
}

/** Badge HTML coloré pour le statut d'un rendez-vous */
function badgeStatutRdv(string $statut): string {
    $map = [
        'en_attente' => ['En attente', 'badge-orange'],
        'accepte'    => ['Accepté',    'badge-vert'],
        'refuse'     => ['Refusé',     'badge-rouge'],
        'termine'    => ['Terminé',    'badge-bleu'],
    ];
    $b = $map[$statut] ?? ['Inconnu', 'badge-gris'];
    return '<span class="badge ' . $b[1] . '">' . $b[0] . '</span>';
}

/** Label lisible du type de bien */
function labelType(string $type): string {
    return match($type) {
        'maison'      => 'Maison',
        'terrain'     => 'Terrain',
        'appartement' => 'Appartement',
        default       => ucfirst($type),
    };
}

// ================================================================
//  FLASH MESSAGES
// ================================================================

/** Enregistre un message flash (succès ou erreur) */
function setFlash(string $type, string $message): void {
    demarrerSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Affiche et supprime le message flash */
function afficherFlash(): void {
    demarrerSession();
    if (!empty($_SESSION['flash'])) {
        $f     = $_SESSION['flash'];
        $class = $f['type'] === 'succes' ? 'alerte-succes' : 'alerte-erreur';
        echo '<div class="alerte ' . $class . '">' . esc($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}

// ================================================================
//  UPLOAD IMAGE
// ================================================================

/**
 * Traite l'upload d'une image de bien
 * @throws RuntimeException si le fichier est invalide
 */
function traiterUploadPhoto(array $fichier): ?string {
    if (empty($fichier['name'])) return null;

    $typesAutorises = ['image/jpeg', 'image/jpg', 'image/png'];
    $tailleMax      = 5 * 1024 * 1024; // 5 Mo

    if (!in_array($fichier['type'], $typesAutorises, true)) {
        throw new RuntimeException('Format non autorisé. Utilisez JPG ou PNG.');
    }
    if ($fichier['size'] > $tailleMax) {
        throw new RuntimeException('Image trop lourde (maximum 5 Mo).');
    }
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erreur lors de l\'upload de l\'image.');
    }

    $ext      = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
    $nomFich  = 'bien_' . uniqid('', true) . '.' . $ext;
    $dest     = __DIR__ . '/../uploads/' . $nomFich;

    if (!move_uploaded_file($fichier['tmp_name'], $dest)) {
        throw new RuntimeException('Impossible d\'enregistrer l\'image sur le serveur.');
    }

    return $nomFich;
}

// ================================================================
//  STATISTIQUES DASHBOARD
// ================================================================

/** Récupère toutes les statistiques pour le tableau de bord */
function obtenirStatistiques(): array {
    $bdd = obtenirBDD();

    // Biens par statut
    $stmt      = $bdd->query("SELECT statut, COUNT(*) AS nb FROM biens GROUP BY statut");
    $parStatut = [];
    foreach ($stmt->fetchAll() as $row) {
        $parStatut[$row['statut']] = (int)$row['nb'];
    }

    // Biens par type
    $stmt    = $bdd->query("SELECT type, COUNT(*) AS nb FROM biens GROUP BY type");
    $parType = [];
    foreach ($stmt->fetchAll() as $row) {
        $parType[$row['type']] = (int)$row['nb'];
    }

    // Biens par ville (top 8)
    $stmt      = $bdd->query("SELECT ville, COUNT(*) AS nb FROM biens GROUP BY ville ORDER BY nb DESC LIMIT 8");
    $parVille  = $stmt->fetchAll();

    // CA total
    $ca = (float)$bdd->query("SELECT COALESCE(SUM(montant), 0) FROM transactions")->fetchColumn();

    return [
        'total_biens'    => array_sum($parStatut),
        'disponibles'    => $parStatut['disponible'] ?? 0,
        'reserves'       => $parStatut['reserve']    ?? 0,
        'vendus'         => $parStatut['vendu']      ?? 0,
        'loues'          => $parStatut['loue']       ?? 0,
        'total_clients'  => (int)$bdd->query("SELECT COUNT(*) FROM clients")->fetchColumn(),
        'rdv_attente'    => (int)$bdd->query("SELECT COUNT(*) FROM rendez_vous WHERE statut='en_attente'")->fetchColumn(),
        'ca_total'       => $ca,
        'par_type'       => $parType,
        'par_ville'      => $parVille,
    ];
}
