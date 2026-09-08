<?php
/**
 * GasyImmo — Modifier un bien immobilier
 * Fichier : admin/bien_modifier.php
 */

require_once __DIR__ . '/../config/fonctions.php';

$bdd = obtenirBDD();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) rediriger('/gasyimmo/admin/biens.php');

$stmt = $bdd->prepare("SELECT * FROM biens WHERE id = ?");
$stmt->execute([$id]);
$bien = $stmt->fetch();

if (!$bien) {
    setFlash('erreur', 'Bien introuvable.');
    rediriger('/gasyimmo/admin/biens.php');
}

$titrePage     = 'Modifier le bien';
$sousTitrePage = 'Édition : ' . $bien['titre'];
$erreurs = [];
$donnees = $bien; // pré-remplissage

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donnees = [
        'titre'       => trim($_POST['titre']       ?? ''),
        'type'        => trim($_POST['type']        ?? ''),
        'ville'       => trim($_POST['ville']       ?? ''),
        'adresse'     => trim($_POST['adresse']     ?? ''),
        'superficie'  => trim($_POST['superficie']  ?? ''),
        'nb_pieces'   => trim($_POST['nb_pieces']   ?? '0'),
        'etage'       => trim($_POST['etage']       ?? ''),
        'prix'        => trim($_POST['prix']        ?? ''),
        'statut'      => trim($_POST['statut']      ?? ''),
        'description' => trim($_POST['description'] ?? ''),
    ];

    if (!$donnees['titre']) $erreurs[] = 'Le titre est obligatoire.';
    if (!is_numeric($donnees['prix']) || (float)$donnees['prix'] <= 0)
        $erreurs[] = 'Le prix doit être un nombre positif.';

    $photo = $bien['photo']; // conserve l'ancienne
    if (!empty($_FILES['photo']['name'])) {
        try {
            $nouvellePhoto = traiterUploadPhoto($_FILES['photo']);
            // Supprime l'ancienne photo
            if ($bien['photo']) {
                $ancienChemin = __DIR__ . '/../uploads/' . $bien['photo'];
                if (file_exists($ancienChemin)) unlink($ancienChemin);
            }
            $photo = $nouvellePhoto;
        } catch (RuntimeException $e) {
            $erreurs[] = $e->getMessage();
        }
    }

    if (empty($erreurs)) {
        $stmt = $bdd->prepare("
            UPDATE biens
            SET titre=?, type=?, ville=?, adresse=?, superficie=?, nb_pieces=?, etage=?,
                prix=?, statut=?, description=?, photo=?
            WHERE id=?
        ");
        $stmt->execute([
            $donnees['titre'], $donnees['type'], $donnees['ville'], $donnees['adresse'],
            $donnees['superficie'] !== '' ? $donnees['superficie'] : null,
            (int)$donnees['nb_pieces'],
            $donnees['etage'] !== '' ? (int)$donnees['etage'] : null,
            $donnees['prix'], $donnees['statut'], $donnees['description'],
            $photo, $id,
        ]);

        setFlash('succes', 'Bien modifié avec succès !');
        rediriger('/gasyimmo/admin/biens.php');
    }
}

require_once __DIR__ . '/../includes/entete_admin.php';
?>

<div class="carte" style="max-width:840px;">
    <div class="carte-entete">
        <span class="carte-titre">Modifier : <?= esc($bien['titre']) ?></span>
        <a href="/gasyimmo/admin/biens.php" class="btn btn-secondaire btn-sm">← Retour</a>
    </div>

    <?php if (!empty($erreurs)): ?>
        <div class="alerte alerte-erreur">
            <?php foreach ($erreurs as $err): ?><div>• <?= esc($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <div class="grille-form" style="margin-bottom:18px;">
            <div class="groupe-form plein">
                <label>Titre *</label>
                <input type="text" name="titre" value="<?= esc($donnees['titre']) ?>" required>
            </div>
            <div class="groupe-form">
                <label>Type *</label>
                <select name="type" required>
                    <option value="maison"      <?= $donnees['type']==='maison'      ?'selected':'' ?>>Maison</option>
                    <option value="appartement" <?= $donnees['type']==='appartement' ?'selected':'' ?>>Appartement</option>
                    <option value="terrain"     <?= $donnees['type']==='terrain'     ?'selected':'' ?>>Terrain</option>
                </select>
            </div>
            <div class="groupe-form">
                <label>Statut</label>
                <select name="statut">
                    <option value="disponible" <?= $donnees['statut']==='disponible'?'selected':'' ?>>Disponible</option>
                    <option value="reserve"    <?= $donnees['statut']==='reserve'   ?'selected':'' ?>>Réservé</option>
                    <option value="vendu"      <?= $donnees['statut']==='vendu'     ?'selected':'' ?>>Vendu</option>
                    <option value="loue"       <?= $donnees['statut']==='loue'      ?'selected':'' ?>>Loué</option>
                </select>
            </div>
            <div class="groupe-form">
                <label>Ville</label>
                <input type="text" name="ville" value="<?= esc($donnees['ville']) ?>">
            </div>
            <div class="groupe-form">
                <label>Adresse</label>
                <input type="text" name="adresse" value="<?= esc($donnees['adresse']) ?>">
            </div>
        </div>

        <div class="grille-form-3" style="margin-bottom:18px;">
            <div class="groupe-form">
                <label>Prix (Ar) *</label>
                <input type="number" name="prix" value="<?= esc($donnees['prix']) ?>" min="0" required>
            </div>
            <div class="groupe-form">
                <label>Superficie (m²)</label>
                <input type="number" name="superficie" value="<?= esc($donnees['superficie']) ?>" min="0" step="0.01">
            </div>
            <div class="groupe-form">
                <label>Pièces</label>
                <input type="number" name="nb_pieces" value="<?= esc($donnees['nb_pieces']) ?>" min="0">
            </div>
            <div class="groupe-form">
                <label>Étage</label>
                <input type="number" name="etage" value="<?= esc($donnees['etage'] ?? '') ?>" min="0">
            </div>
        </div>

        <div class="groupe-form" style="margin-bottom:18px;">
            <label>Description</label>
            <textarea name="description" rows="5"><?= esc($donnees['description']) ?></textarea>
        </div>

        <!-- Photo actuelle + remplacement -->
        <div class="groupe-form" style="margin-bottom:24px;">
            <label>Photo</label>
            <?php if ($bien['photo']): ?>
                <div style="margin-bottom:10px;">
                    <img src="/gasyimmo/uploads/<?= esc($bien['photo']) ?>"
                         style="height:110px;border-radius:8px;object-fit:cover;border:1px solid #dde8e2;"
                         alt="Photo actuelle">
                    <p style="font-size:11.5px;color:#7a9e89;margin-top:4px;">Photo actuelle — choisissez-en une nouvelle pour la remplacer.</p>
                </div>
            <?php endif; ?>
            <div class="zone-upload" onclick="document.getElementById('photo').click()">
                <p style="color:#3d5c48;font-size:13px;">Cliquer pour changer la photo</p>
                <p style="color:#7a9e89;font-size:12px;">JPG, JPEG, PNG — Max 5 Mo</p>
                <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png" style="display:none;">
            </div>
            <img id="apercu-photo" class="apercu-photo" src="" alt="Aperçu">
        </div>

        <div class="actions-form">
            <button type="submit" class="btn btn-primaire">Enregistrer les modifications</button>
            <a href="/gasyimmo/admin/biens.php" class="btn btn-secondaire">Annuler</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/pied_admin.php'; ?>
