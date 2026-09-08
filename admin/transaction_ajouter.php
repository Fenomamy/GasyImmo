<?php
/**
 * GasyImmo — Ajouter une transaction (vente ou location)
 * Fichier : admin/transaction_ajouter.php
 * Met à jour automatiquement le statut du bien
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage     = 'Nouvelle transaction';
$sousTitrePage = 'Enregistrer une vente ou une location';

$bdd     = obtenirBDD();
$erreurs = [];

// Récupère les biens disponibles ou réservés (éligibles à une transaction)
$biensDisponibles = $bdd->query("
    SELECT id, titre, ville, prix, statut, type
    FROM biens
    WHERE statut IN ('disponible','reserve')
    ORDER BY titre
")->fetchAll();

// Récupère tous les clients
$clients = $bdd->query("SELECT id, nom, prenom, cin FROM clients ORDER BY nom, prenom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id       = (int)trim($_POST['client_id']       ?? 0);
    $bien_id         = (int)trim($_POST['bien_id']         ?? 0);
    $type_transaction= trim($_POST['type_transaction']     ?? '');
    $montant         = trim($_POST['montant']              ?? '');
    $date_transaction= trim($_POST['date_transaction']     ?? '');
    $notes           = trim($_POST['notes']                ?? '');

    // Validations
    if (!$client_id)       $erreurs[] = 'Veuillez sélectionner un client.';
    if (!$bien_id)         $erreurs[] = 'Veuillez sélectionner un bien.';
    if (!in_array($type_transaction, ['vente','location']))
        $erreurs[] = 'Le type de transaction est invalide.';
    if (!is_numeric($montant) || (float)$montant <= 0)
        $erreurs[] = 'Le montant doit être un nombre positif.';
    if (!$date_transaction)
        $erreurs[] = 'La date de transaction est obligatoire.';

    // Vérification : le bien n'a pas déjà une transaction finale
    if ($bien_id && empty($erreurs)) {
        $stmtBien = $bdd->prepare("SELECT * FROM biens WHERE id = ?");
        $stmtBien->execute([$bien_id]);
        $bien = $stmtBien->fetch();

        if (!$bien) {
            $erreurs[] = 'Bien introuvable.';
        } elseif (in_array($bien['statut'], ['vendu','loue'])) {
            $erreurs[] = 'Ce bien a déjà une transaction finale (vendu ou loué).';
        }
    }

    if (empty($erreurs)) {
        // Enregistre la transaction
        $bdd->prepare("
            INSERT INTO transactions (client_id, bien_id, type_transaction, montant, date_transaction, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([$client_id, $bien_id, $type_transaction, $montant, $date_transaction, $notes]);

        // Met à jour le statut du bien
        $nouveauStatut = $type_transaction === 'vente' ? 'vendu' : 'loue';
        $bdd->prepare("UPDATE biens SET statut = ? WHERE id = ?")
            ->execute([$nouveauStatut, $bien_id]);

        setFlash('succes', 'Transaction enregistrée avec succès. Statut du bien mis à jour : ' . ($nouveauStatut === 'vendu' ? 'Vendu' : 'Loué'));
        rediriger('/gasyimmo/admin/transactions.php');
    }
}

require_once __DIR__ . '/../includes/entete_admin.php';
?>

<div class="carte" style="max-width:720px;">
    <div class="carte-entete">
        <span class="carte-titre">Enregistrer une transaction</span>
        <a href="/gasyimmo/admin/transactions.php" class="btn btn-secondaire btn-sm">← Retour</a>
    </div>

    <?php if (!empty($erreurs)): ?>
        <div class="alerte alerte-erreur">
            <?php foreach ($erreurs as $err): ?><div>• <?= esc($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Info métier -->
    <div style="background:#f0fdf4;border:1px solid #dcfce7;border-radius:8px;padding:14px 18px;margin-bottom:22px;font-size:13px;color:#15803d;line-height:1.7;">
        <strong>ℹ️ Règles :</strong><br>
        • Seuls les biens <strong>disponibles ou réservés</strong> peuvent faire l'objet d'une transaction.<br>
        • Après validation, le statut du bien sera automatiquement mis à jour (<em>vendu</em> ou <em>loué</em>).
    </div>

    <form method="POST">
        <div class="grille-form" style="margin-bottom:18px;">

            <!-- Client -->
            <div class="groupe-form">
                <label for="client_id">Client *</label>
                <select id="client_id" name="client_id" required>
                    <option value="">— Sélectionner un client —</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= (isset($_POST['client_id']) && (int)$_POST['client_id'] === $c['id']) ? 'selected' : '' ?>>
                            <?= esc($c['prenom'] . ' ' . $c['nom']) ?> — CIN: <?= esc($c['cin']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($clients)): ?>
                    <span class="indice-form" style="color:#dc2626;">
                        Aucun client. <a href="/gasyimmo/admin/clients.php" style="color:#dc2626;">Ajoutez un client d'abord.</a>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Bien -->
            <div class="groupe-form">
                <label for="bien_id">Bien immobilier *</label>
                <select id="bien_id" name="bien_id" required onchange="remplirPrix(this)">
                    <option value="">— Sélectionner un bien —</option>
                    <?php foreach ($biensDisponibles as $b): ?>
                        <option value="<?= $b['id'] ?>"
                                data-prix="<?= $b['prix'] ?>"
                            <?= (isset($_POST['bien_id']) && (int)$_POST['bien_id'] === $b['id']) ? 'selected' : '' ?>>
                            <?= esc($b['titre']) ?> — <?= esc($b['ville']) ?>
                            (<?= badgeStatutBien($b['statut']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($biensDisponibles)): ?>
                    <span class="indice-form" style="color:#dc2626;">
                        Aucun bien disponible ou réservé.
                    </span>
                <?php endif; ?>
            </div>

            <!-- Type transaction -->
            <div class="groupe-form">
                <label for="type_transaction">Type de transaction *</label>
                <select id="type_transaction" name="type_transaction" required>
                    <option value="">— Choisir —</option>
                    <option value="vente"    <?= ($_POST['type_transaction']??'')==='vente'    ?'selected':'' ?>>Vente</option>
                    <option value="location" <?= ($_POST['type_transaction']??'')==='location' ?'selected':'' ?>>Location</option>
                </select>
            </div>

            <!-- Montant -->
            <div class="groupe-form">
                <label for="montant">Montant (Ar) *</label>
                <input type="number" id="montant" name="montant" min="0" step="1000"
                       value="<?= esc($_POST['montant'] ?? '') ?>"
                       placeholder="Ex: 180000000" required>
                <span class="indice-form">Prix indicatif du bien sélectionné — vous pouvez le modifier.</span>
            </div>

            <!-- Date -->
            <div class="groupe-form">
                <label for="date_transaction">Date de transaction *</label>
                <input type="date" id="date_transaction" name="date_transaction"
                       value="<?= esc($_POST['date_transaction'] ?? date('Y-m-d')) ?>" required>
            </div>

        </div>

        <!-- Notes -->
        <div class="groupe-form" style="margin-bottom:24px;">
            <label for="notes">Notes / Observations</label>
            <textarea id="notes" name="notes" rows="4"
                      placeholder="Ex: Acte notarié signé, bail signé, conditions particulières..."><?= esc($_POST['notes'] ?? '') ?></textarea>
        </div>

        <div class="actions-form">
            <button type="submit" class="btn btn-primaire">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                </svg>
                Enregistrer la transaction
            </button>
            <a href="/gasyimmo/admin/transactions.php" class="btn btn-secondaire">Annuler</a>
        </div>
    </form>
</div>

<script>
/* Pré-remplit le montant avec le prix du bien sélectionné */
function remplirPrix(select) {
    var option  = select.options[select.selectedIndex];
    var prix    = option.dataset.prix;
    var montant = document.getElementById('montant');
    if (prix && montant && !montant.value) {
        montant.value = prix;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/pied_admin.php'; ?>
