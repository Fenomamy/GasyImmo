<?php
/**
 * GasyImmo — Page À propos & Contact
 * Fichier : pages/a_propos.php
 */

require_once __DIR__ . '/../config/fonctions.php';

$titrePage = 'À propos de GasyImmo';

$bdd     = obtenirBDD();
$erreurs = [];
$succes  = false;

// ── Traitement formulaire de contact ──────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $email   = trim($_POST['email']   ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$nom)                        $erreurs[] = 'Le nom est obligatoire.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = 'Adresse email invalide.';
    if (!$message)                    $erreurs[] = 'Le message est obligatoire.';

    if (empty($erreurs)) {
        $bdd->prepare("INSERT INTO messages_contact (nom, email, message) VALUES (?,?,?)")
            ->execute([$nom, $email, $message]);
        $succes = true;
        $_POST  = [];
    }
}

require_once __DIR__ . '/../includes/entete_public.php';
?>

<!-- Bannière -->
<div class="banniere-page">
    <div class="conteneur">
        <div class="banniere-nav">
            <div>
                <h1>À propos de GasyImmo</h1>
                <p>Votre agence immobilière de confiance à Madagascar</p>
            </div>
            <a href="/gasyimmo/pages/accueil.php" class="btn-retour">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<!-- Mission -->
<section class="section section-alt">
    <div class="conteneur">
        <div class="section-entete">
            <span class="section-label">Notre histoire</span>
            <h2 class="section-titre">L'immobilier malgache, notre passion</h2>
            <p class="section-sous-titre">
                Depuis plus de 10 ans, GasyImmo accompagne les Malgaches dans leurs projets immobiliers.
                Acheter, vendre, louer : nous sommes à vos côtés à chaque étape.
            </p>
        </div>

        <div class="grille-biens" style="grid-template-columns:repeat(3,1fr);">
            <div style="background:#fff;border-radius:14px;padding:26px;border:1px solid #dde8e2;text-align:center;">
                <div style="font-size:38px;margin-bottom:12px;">🏆</div>
                <h3 style="font-size:16px;font-weight:700;margin-bottom:8px;">Notre mission</h3>
                <p style="font-size:13.5px;color:#3d5c48;line-height:1.7;">
                    Faciliter l'accès à la propriété pour tous les Malgaches, en proposant un service transparent, professionnel et humain.
                </p>
            </div>
            <div style="background:#fff;border-radius:14px;padding:26px;border:1px solid #dde8e2;text-align:center;">
                <div style="font-size:38px;margin-bottom:12px;">🌍</div>
                <h3 style="font-size:16px;font-weight:700;margin-bottom:8px;">Notre couverture</h3>
                <p style="font-size:13.5px;color:#3d5c48;line-height:1.7;">
                    Présents dans les principales villes : Antananarivo, Toamasina, Mahajanga, Fianarantsoa et Antsirabe.
                </p>
            </div>
            <div style="background:#fff;border-radius:14px;padding:26px;border:1px solid #dde8e2;text-align:center;">
                <div style="font-size:38px;margin-bottom:12px;">🤝</div>
                <h3 style="font-size:16px;font-weight:700;margin-bottom:8px;">Notre engagement</h3>
                <p style="font-size:13.5px;color:#3d5c48;line-height:1.7;">
                    Confiance, transparence et excellence dans chaque transaction. Votre satisfaction est notre priorité absolue.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact -->
<section class="section">
    <div class="conteneur">
        <div class="contact-grille">

            <!-- Informations de contact -->
            <div class="contact-info">
                <h2>Nous contacter</h2>
                <p>
                    Vous avez une question, un projet immobilier ou souhaitez simplement plus d'informations ?
                    Notre équipe est disponible du lundi au vendredi de 08h à 17h.
                </p>

                <div class="contact-item">
                    <div class="contact-icone">📍</div>
                    <div class="contact-texte">
                        <strong>Adresse</strong>
                        <span>Lot II A 47, Analakely<br>Antananarivo 101 — Madagascar</span>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icone">📞</div>
                    <div class="contact-texte">
                        <strong>Téléphone</strong>
                        <span>034 00 000 00<br>033 00 000 00 (WhatsApp)</span>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icone">✉️</div>
                    <div class="contact-texte">
                        <strong>Email</strong>
                        <span>contact@gasyimmo.mg<br>info@gasyimmo.mg</span>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icone">🕐</div>
                    <div class="contact-texte">
                        <strong>Horaires d'ouverture</strong>
                        <span>
                            Lundi — Vendredi : 08h00 – 17h00<br>
                            Samedi : 09h00 – 12h00<br>
                            Dimanche : Fermé
                        </span>
                    </div>
                </div>

                <!-- Carte simple CSS -->
                <div style="background:linear-gradient(135deg,#0a2116,#1a4a2a);border-radius:14px;padding:22px;color:rgba(255,255,255,.7);margin-top:8px;">
                    <h4 style="color:#4ade80;font-size:14px;font-weight:700;margin-bottom:10px;">📌 GasyImmo — Siège social</h4>
                    <p style="font-size:13px;line-height:1.8;">
                        Lot II A 47, Analakely<br>
                        Antananarivo 101<br>
                        République de Madagascar<br>
                        <a href="tel:+261340000000" style="color:#4ade80;font-weight:600;">+261 34 00 000 00</a>
                    </p>
                </div>
            </div>

            <!-- Formulaire de contact -->
            <div class="form-contact-enveloppe">
                <h3>Envoyer un message</h3>

                <?php if ($succes): ?>
                    <div class="pub-alerte pub-alerte-succes">
                        ✅ Votre message a bien été envoyé ! Nous vous répondrons dans les 24 heures.
                    </div>
                <?php endif; ?>

                <?php if (!empty($erreurs)): ?>
                    <div class="pub-alerte pub-alerte-erreur">
                        <?php foreach ($erreurs as $err): ?><div>• <?= esc($err) ?></div><?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="formulaire-contact" novalidate>
                    <div class="contact-groupe">
                        <label for="c_nom">Nom complet *</label>
                        <input type="text" id="c_nom" name="nom"
                               value="<?= esc($_POST['nom'] ?? '') ?>"
                               placeholder="Jean Rakoto" required>
                        <span class="msg-erreur"></span>
                    </div>
                    <div class="contact-groupe">
                        <label for="c_email">Adresse email *</label>
                        <input type="email" id="c_email" name="email"
                               value="<?= esc($_POST['email'] ?? '') ?>"
                               placeholder="jean.rakoto@email.mg" required>
                        <span class="msg-erreur"></span>
                    </div>
                    <div class="contact-groupe">
                        <label for="c_message">Message *</label>
                        <textarea id="c_message" name="message" rows="6"
                                  placeholder="Décrivez votre projet immobilier ou posez-nous votre question..."
                                  required><?= esc($_POST['message'] ?? '') ?></textarea>
                        <span class="msg-erreur"></span>
                    </div>
                    <button type="submit" class="contact-submit">
                        Envoyer le message
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<!-- Équipe -->
<section class="section section-alt">
    <div class="conteneur">
        <div class="section-entete">
            <span class="section-label">Notre équipe</span>
            <h2 class="section-titre">Des professionnels à votre service</h2>
        </div>

        <div class="grille-biens" style="grid-template-columns:repeat(3,1fr);">
            <?php
            $equipe = [
                ['initiales'=>'RA','nom'=>'Rakoto Andry','poste'=>'Directeur Général','bio'=>'15 ans d\'expérience dans l\'immobilier malgache.'],
                ['initiales'=>'MB','nom'=>'Marie Rabe','poste'=>'Responsable commerciale','bio'=>'Spécialiste des transactions résidentielles à Antananarivo.'],
                ['initiales'=>'PL','nom'=>'Paul Luc Rasolofo','poste'=>'Conseiller immobilier','bio'=>'Expert en biens fonciers et terrains constructibles.'],
            ];
            foreach ($equipe as $m):
            ?>
            <div style="background:#fff;border-radius:14px;padding:26px;border:1px solid #dde8e2;text-align:center;">
                <div style="width:72px;height:72px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;font-weight:800;color:#1a6b3a;">
                    <?= $m['initiales'] ?>
                </div>
                <h3 style="font-size:16px;font-weight:700;margin-bottom:4px;"><?= esc($m['nom']) ?></h3>
                <p style="font-size:12.5px;color:#1a6b3a;font-weight:600;margin-bottom:10px;"><?= esc($m['poste']) ?></p>
                <p style="font-size:13px;color:#3d5c48;line-height:1.6;"><?= esc($m['bio']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/pied_public.php'; ?>
