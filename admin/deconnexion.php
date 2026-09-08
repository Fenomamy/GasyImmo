<?php
/**
 * GasyImmo — Déconnexion admin
 * Fichier : admin/deconnexion.php
 */

require_once __DIR__ . '/../config/fonctions.php';

demarrerSession();
session_destroy();

// Redirige vers la page de connexion
rediriger('/gasyimmo/admin/connexion.php');
