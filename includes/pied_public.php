<?php
/**
 * GasyImmo — Pied de page public
 * Fichier : includes/pied_public.php
 */
?>
<footer class="pied-page">
    <div class="pied-conteneur">
        <div class="pied-grille">

            <!-- Marque -->
            <div class="pied-marque">
                <a href="/gasyimmo/pages/accueil.php" class="nav-logo">
                    <div class="nav-logo-icone">
                        <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="36" height="36" rx="10" fill="#1a6b3a"/>
                            <path d="M18 7L29 15V31H23V24H13V31H7V15L18 7Z" fill="white"/>
                            <circle cx="18" cy="18" r="2.5" fill="#4ade80"/>
                        </svg>
                    </div>
                    <span style="color:#fff;font-weight:800;font-size:18px;">GasyImmo</span>
                </a>
                <p>Votre partenaire immobilier de confiance à Madagascar. Maisons, appartements et terrains.</p>
            </div>

            <!-- Navigation -->
            <div class="pied-col">
                <h4>Navigation</h4>
                <a href="/gasyimmo/pages/accueil.php">Accueil</a>
                <a href="/gasyimmo/pages/biens.php">Nos biens</a>
                <a href="/gasyimmo/pages/rendez_vous.php">Rendez-vous</a>
                <a href="/gasyimmo/pages/a_propos.php">À propos</a>
            </div>

            <!-- Contact -->
            <div class="pied-col">
                <h4>Contact</h4>
                <p>📍 Analakely, Antananarivo 101</p>
                <p>📞 034 00 000 00</p>
                <p>✉️ contact@gasyimmo.mg</p>
                <p>🕐 Lun–Ven : 08h–17h</p>
            </div>

        </div>

        <div class="pied-bas">
            <p>© <?= date('Y') ?> GasyImmo — Projet universitaire L2 Informatique — Madagascar</p>
        </div>
    </div>
</footer>

<script src="/gasyimmo/assets/js/public.js"></script>
</body>
</html>
