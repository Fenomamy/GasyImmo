<?php
/**
 * GasyImmo — Ajouter un bien immobilier
 * Fichier : admin/bien_ajouter.php
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage     = 'Ajouter un bien';
$sousTitrePage = 'Enregistrer un nouveau bien immobilier';

$erreurs = [];
$donnees = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donnees = [
        'titre'       => trim($_POST['titre']       ?? ''),
        'type'        => trim($_POST['type']        ?? ''),
        'ville'       => trim($_POST['ville']        ?? ''),
        'adresse'     => trim($_POST['adresse']     ?? ''),
        'superficie'  => trim($_POST['superficie']  ?? ''),
        'nb_pieces'   => trim($_POST['nb_pieces']   ?? '0'),
        'etage'       => trim($_POST['etage']       ?? ''),
        'prix'        => trim($_POST['prix']        ?? ''),
        'statut'      => trim($_POST['statut']      ?? 'disponible'),
        'description' => trim($_POST['description'] ?? ''),
    ];

    // Validations
    if (!$donnees['titre'])   $erreurs[] = 'Le titre est obligatoire.';
    if (!$donnees['type'])    $erreurs[] = 'Le type de bien est obligatoire.';
    if (!$donnees['ville'])   $erreurs[] = 'La ville est obligatoire.';
    if (!is_numeric($donnees['prix']) || (float)$donnees['prix'] <= 0)
        $erreurs[] = 'Le prix doit être un nombre positif.';

    // Photo
    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        try {
            $photo = traiterUploadPhoto($_FILES['photo']);
        } catch (RuntimeException $e) {
            $erreurs[] = $e->getMessage();
        }
    }

    if (empty($erreurs)) {
        $bdd  = obtenirBDD();
        $stmt = $bdd->prepare("
            INSERT INTO biens
                (titre, type, ville, adresse, superficie, nb_pieces, etage, prix, statut, description, photo)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $donnees['titre'],
            $donnees['type'],
            $donnees['ville'],
            $donnees['adresse'],
            $donnees['superficie'] !== '' ? $donnees['superficie'] : null,
            (int)$donnees['nb_pieces'],
            $donnees['etage'] !== '' ? (int)$donnees['etage'] : null,
            $donnees['prix'],
            $donnees['statut'],
            $donnees['description'],
            $photo,
        ]);

        setFlash('succes', 'Le bien "' . $donnees['titre'] . '" a été ajouté avec succès !');
        rediriger('/gasyimmo/admin/biens.php');
    }
}

require_once __DIR__ . '/../includes/entete_admin.php';
?>

<div class="carte" style="max-width:840px;">
    <div class="carte-entete">
        <span class="carte-titre">Informations du bien</span>
        <a href="/gasyimmo/admin/biens.php" class="btn btn-secondaire btn-sm">← Retour</a>
    </div>

    <?php if (!empty($erreurs)): ?>
        <div class="alerte alerte-erreur">
            <?php foreach ($erreurs as $err): ?><div>• <?= esc($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <!-- Infos générales -->
        <div class="grille-form" style="margin-bottom:18px;">
            <div class="groupe-form plein">
                <label for="titre">Titre du bien *</label>
                <input type="text" id="titre" name="titre"
                       value="<?= esc($donnees['titre'] ?? '') ?>"
                       placeholder="Ex: Belle villa avec jardin à Antananarivo" required>
            </div>
            <div class="groupe-form">
                <label for="type">Type *</label>
                <select id="type" name="type" required>
                    <option value="">— Sélectionner —</option>
                    <option value="maison"      <?= ($donnees['type']??'')==='maison'      ?'selected':'' ?>>Maison</option>
                    <option value="appartement" <?= ($donnees['type']??'')==='appartement' ?'selected':'' ?>>Appartement</option>
                    <option value="terrain"     <?= ($donnees['type']??'')==='terrain'     ?'selected':'' ?>>Terrain</option>
                </select>
            </div>
            <div class="groupe-form">
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <option value="disponible" <?= ($donnees['statut']??'')==='disponible'?'selected':'' ?>>Disponible</option>
                    <option value="reserve"    <?= ($donnees['statut']??'')==='reserve'   ?'selected':'' ?>>Réservé</option>
                    <option value="vendu"      <?= ($donnees['statut']??'')==='vendu'     ?'selected':'' ?>>Vendu</option>
                    <option value="loue"       <?= ($donnees['statut']??'')==='loue'      ?'selected':'' ?>>Loué</option>
                </select>
            </div>
            <div class="groupe-form">
                <label for="ville">Ville *</label>
                <input type="text" id="ville" name="ville"
                       value="<?= esc($donnees['ville'] ?? '') ?>"
                       placeholder="Ex: Antananarivo" required>
            </div>
            <div class="groupe-form">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse"
                       value="<?= esc($donnees['adresse'] ?? '') ?>"
                       placeholder="Ex: Lot II A 47, Ambohijanaka">
            </div>
        </div>

        <!-- Caractéristiques -->
        <div class="grille-form-3" style="margin-bottom:18px;">
            <div class="groupe-form">
                <label for="prix">Prix (Ar) *</label>
                <input type="number" id="prix" name="prix" min="0" step="1000"
                       value="<?= esc($donnees['prix'] ?? '') ?>"
                       placeholder="Ex: 150000000" required>
            </div>
            <div class="groupe-form">
                <label for="superficie">Superficie (m²)</label>
                <input type="number" id="superficie" name="superficie" min="0" step="0.01"
                       value="<?= esc($donnees['superficie'] ?? '') ?>" placeholder="Ex: 250">
            </div>
            <div class="groupe-form">
                <label for="nb_pieces">Nombre de pièces</label>
                <input type="number" id="nb_pieces" name="nb_pieces" min="0"
                       value="<?= esc($donnees['nb_pieces'] ?? '0') ?>">
            </div>
            <div class="groupe-form">
                <label for="etage">Étage <span style="font-weight:400;text-transform:none;">(si applicable)</span></label>
                <input type="number" id="etage" name="etage" min="0"
                       value="<?= esc($donnees['etage'] ?? '') ?>"
                       placeholder="Laisser vide si non applicable">
            </div>
        </div>

        <!-- Description -->
        <div class="groupe-form" style="margin-bottom:18px;">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"
                      placeholder="Décrivez le bien : état, équipements, environnement..."><?= esc($donnees['description'] ?? '') ?></textarea>
        </div>

        <!-- Photo -->
        <div class="groupe-form" style="margin-bottom:24px;">
            <label>Photo du bien <span style="font-weight:400;text-transform:none;">(JPG, PNG — max 5 Mo)</span></label>
            <div class="zone-upload" onclick="document.getElementById('photo').click()">
                <svg viewBox="0 0 24 24" fill="none" stroke="#7a9e89" stroke-width="1.5" width="34" height="34" style="margin:0 auto 8px;">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
                <p style="color:#3d5c48;font-size:13.5px;font-weight:500;">Cliquez pour sélectionner une image</p>
                <p style="color:#7a9e89;font-size:12px;margin-top:3px;">Formats acceptés : JPG, JPEG, PNG</p>
                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png"
                       style="display:none;">
            </div>
            <img id="apercu-photo" class="apercu-photo" src="" alt="Aperçu">
        </div>

        <div class="actions-form">
            <button type="submit" class="btn btn-primaire">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                </svg>
                Enregistrer le bien
            </button>
            <a href="/gasyimmo/admin/biens.php" class="btn btn-secondaire">Annuler</a>
        </div>

    </form>
</div>

<?php require_once __DIR__ . '/../includes/pied_admin.php'; ?>
